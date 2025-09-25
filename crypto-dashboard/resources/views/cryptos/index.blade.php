<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Listado De Criptomonedas</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <style>
    body{background:#0f1115;color:#e6e6e6}
    .table thead th{position:sticky;top:0;background:#151923;z-index:1}
    .badge-dot{display:inline-block;width:.6rem;height:.6rem;border-radius:50%;margin-right:.35rem}
    .badge-ok{background:#16a34a}.badge-warn{background:#f59e0b}.badge-err{background:#ef4444}
  </style>
</head>
<body>

<div class="container py-4">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
    <h1 class="m-0">Listado De Criptomonedas</h1>

    <div class="d-flex gap-2 align-items-center">
      <input id="symbolInput" list="suggestions" class="form-control form-control-sm"
             placeholder="Símbolo o nombre (p. ej. BTC / Dogecoin)" style="width: 280px">
      <datalist id="suggestions"></datalist>
      <button id="addBtn" class="btn btn-sm btn-primary">Agregar símbolo</button>
      <button id="refreshBtn" class="btn btn-sm btn-outline-light">Actualizar ahora</button>
      <span id="statusBadge" class="ms-2 small text-muted"></span>
    </div>
  </div>

  <div id="alertBox" class="alert d-none" role="alert"></div>

  <div class="table-responsive">
    <table class="table table-dark table-hover text-center align-middle mb-0" id="list">
      <thead>
        <tr>
          <th style="width:10%">Símbolo</th>
          <th style="width:25%">Nombre</th>
          <th style="width:20%">Precio (USD)</th>
          <th style="width:25%">Market Cap</th>
          <th style="width:15%">Volumen 24h</th>
          <th style="width:5%">Acciones</th>
        </tr>
      </thead>
      <tbody id="tbody-cryptos">
        {{-- Fallback SSR inicial (opcional) --}}
        @isset($cryptos)
          @foreach($cryptos as $crypto)
            @php $p = optional($crypto->prices->first()); @endphp
            <tr>
              <td><strong>{{ $crypto->symbol }}</strong></td>
              <td>{{ $crypto->name }}</td>
              <td>{{ $p->price_usd !== null ? '$'.number_format($p->price_usd, 2) : '–' }}</td>
              <td>{{ $p->market_cap !== null ? '$'.number_format($p->market_cap, 0) : '–' }}</td>
              <td>{{ $p->volume_24h !== null ? '$'.number_format($p->volume_24h, 0) : '–' }}</td>
              <td><button class="btn btn-sm btn-outline-danger" disabled>Quitar</button></td>
            </tr>
          @endforeach
        @endisset
      </tbody>
    </table>
  </div>
</div>

<script>
 
  const fmtUSD = new Intl.NumberFormat('es-CO', { style:'currency', currency:'USD', maximumFractionDigits: 6 });
  const $ = sel => document.querySelector(sel);
  const $$ = sel => Array.from(document.querySelectorAll(sel));

  function setStatus(kind, text) {
    const dot =
      kind === 'ok'   ? '<span class="badge-dot badge-ok"></span>' :
      kind === 'warn' ? '<span class="badge-dot badge-warn"></span>' :
                        '<span class="badge-dot badge-err"></span>';
    $('#statusBadge').innerHTML = dot + (text || '');
  }

  function showAlert(type, msg) {
    const box = $('#alertBox');
    box.className = `alert alert-${type}`;
    box.textContent = msg;
    box.classList.remove('d-none');
    setTimeout(() => box.classList.add('d-none'), 3500);
  }

  
  async function j(url, opt = {}) {
    const merged = {
      headers: { Accept: 'application/json', ...(opt.headers || {}) },
      ...opt
    };
    const res = await fetch(url, merged);
    if (!res.ok) {
      const txt = await res.text().catch(()=> '');
      throw new Error(`HTTP ${res.status} ${url}\n${txt}`);
    }
 
    const text = await res.text();
    return text ? JSON.parse(text) : null;
  }


  async function refresh(){
    setStatus('warn','Actualizando…');
    try {
      const list = await j('/api/watchlist');

      (list || []).sort((a,b)=> a.symbol.localeCompare(b.symbol));

      const rows = (list || []).map(x => `
        <tr>
          <td><strong>${x.symbol}</strong></td>
          <td>${x.name ?? ''}</td>
          <td>${x.last_price_usd != null ? fmtUSD.format(Number(x.last_price_usd)) : '–'}</td>
          <td>${x.market_cap     != null ? fmtUSD.format(Number(x.market_cap))     : '–'}</td>
          <td>${x.volume_24h     != null ? fmtUSD.format(Number(x.volume_24h))     : '–'}</td>
          <td>
            <button class="btn btn-sm btn-outline-danger" data-remove="${x.symbol}">Quitar</button>
          </td>
        </tr>`).join('');

      $('#tbody-cryptos').innerHTML = rows || `
        <tr><td colspan="6" class="text-muted">Aún no has agregado símbolos. Escribe arriba (p. ej. BTC o “Dogecoin”) y pulsa “Agregar símbolo”.</td></tr>
      `;


      $("[data-remove]") 
      document.querySelectorAll('[data-remove]').forEach(btn => {
        btn.addEventListener('click', async () => {
          const sym = btn.getAttribute('data-remove');
          try {
            await j('/api/watchlist/' + encodeURIComponent(sym), { method:'DELETE' });
            await refresh();
          } catch(e) {
            console.error(e);
            showAlert('danger', 'No se pudo quitar el símbolo.');
          }
        });
      });

      setStatus('ok','Actualizado');
    } catch (e) {
      console.error(e);
      setStatus('err','Error al actualizar');
      showAlert('danger','Error al obtener tu lista. Revisa el servidor.');
    }
  }

  
  async function addSymbol(){
    const input = $('#symbolInput');
    let raw = input.value.trim();
    if (!raw) return;

    
    let symbol = raw.toUpperCase();
    const looksLikeName = /[a-z\s]/.test(raw); 

    if (looksLikeName) {
      try {
        const res = await j('/api/cryptos/search?q=' + encodeURIComponent(raw));
        if (Array.isArray(res) && res.length) {
          
          symbol = String(res[0].symbol || raw).toUpperCase();
        }
      } catch (e) {
        console.warn('No se pudo resolver nombre a símbolo:', e);
      }
    }

 
    const btn = $('#addBtn');
    btn.disabled = true;
    try {
      await j('/api/watchlist', {
        method:'POST',
        headers:{ 'Content-Type':'application/json', 'Accept':'application/json' },
        body: JSON.stringify({ symbol })
      });
      input.value = '';
      await refresh();
      showAlert('success', `Se agregó ${symbol}. Si no ves precio aún, ejecuta el poller.`);
    } catch(e) {
      console.error(e);
      showAlert('danger', 'No se pudo agregar el símbolo.');
    } finally {
      btn.disabled = false;
    }
  }

 
  let lastQ = '';
  $('#symbolInput').addEventListener('input', async (ev) => {
    const q = ev.target.value.trim();
    if (q.length < 1 || q === lastQ) return;
    lastQ = q;
    try {
      const res = await j('/api/cryptos/search?q=' + encodeURIComponent(q));
      const html = (res || [])
        .slice(0, 10)
        .map(r => `<option value="${r.symbol}">${r.name}</option>`)
        .join('');
      $('#suggestions').innerHTML = html;
    } catch (_) {}
  });

  
  $('#addBtn').addEventListener('click', addSymbol);
  $('#refreshBtn').addEventListener('click', refresh);
  $('#symbolInput').addEventListener('keydown', (e)=>{
    if (e.key === 'Enter') {
      e.preventDefault();
      addSymbol();
    }
  });

  (async () => {
    await refresh();
    setInterval(refresh, 60000);
  })();
</script>
</body>
</html>



