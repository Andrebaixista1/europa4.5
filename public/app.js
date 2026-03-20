const DEFAULT_ENDPOINTS = [
  { key: "health", label: "health-consult", path: "/health-consult" },
  { key: "filaIn100", label: "dashboard/fila/in100", path: "/dashboard/fila/in100" },
  { key: "saldoHandMais", label: "dashboard/saldos/handmais", path: "/dashboard/saldos/handmais" },
  { key: "saldoV8", label: "dashboard/saldos/v8", path: "/dashboard/saldos/v8" },
  { key: "saldoPresenca", label: "dashboard/saldos/presenca", path: "/dashboard/saldos/presenca" },
  { key: "saldoIn100", label: "dashboard/saldos/in100", path: "/dashboard/saldos/in100" },
  { key: "saldoPrata", label: "dashboard/saldos/prata", path: "/dashboard/saldos/prata" },
  { key: "consultaHandMais", label: "dashboard/consultas/handmais", path: "/dashboard/consultas/handmais" },
  { key: "consultaV8", label: "dashboard/consultas/v8", path: "/dashboard/consultas/v8" },
  { key: "consultaPresenca", label: "dashboard/consultas/presenca", path: "/dashboard/consultas/presenca" },
  { key: "consultaIn100", label: "dashboard/consultas/in100", path: "/dashboard/consultas/in100" },
  { key: "consultaPrata", label: "dashboard/consultas/prata", path: "/dashboard/consultas/prata" }
];

const state = {
  apiBaseUrl: localStorage.getItem("europa45_api_base") || window.EUROPA45.apiBaseUrl,
  results: {}
};

const els = {
  refreshButton: document.getElementById("refreshButton"),
  saveApiBaseUrlButton: document.getElementById("saveApiBaseUrlButton"),
  apiBaseUrlInput: document.getElementById("apiBaseUrlInput"),
  endpointStatus: document.getElementById("endpointStatus"),
  payloadPreview: document.getElementById("payloadPreview"),
  healthValue: document.getElementById("healthValue"),
  filaIn100Value: document.getElementById("filaIn100Value"),
  saldosCountValue: document.getElementById("saldosCountValue"),
  consultasCountValue: document.getElementById("consultasCountValue"),
  lastUpdate: document.getElementById("lastUpdate")
};

function normalizeBase(base) {
  return String(base || "").replace(/\/+$/, "");
}

function formatTimestamp(date = new Date()) {
  return date.toLocaleString("pt-BR");
}

function parseNumeric(value) {
  if (typeof value === "number" && Number.isFinite(value)) return value;
  if (typeof value === "string") {
    const parsed = Number(value.replace(",", "."));
    if (Number.isFinite(parsed)) return parsed;
  }
  return null;
}

function extractBestNumber(payload) {
  if (parseNumeric(payload) !== null) return parseNumeric(payload);
  if (Array.isArray(payload)) return payload.length;
  if (!payload || typeof payload !== "object") return null;

  const preferredKeys = ["total", "saldo", "saldos", "count", "quantidade", "fila"];
  for (const key of preferredKeys) {
    if (Object.prototype.hasOwnProperty.call(payload, key)) {
      const value = parseNumeric(payload[key]);
      if (value !== null) return value;
    }
  }

  for (const key of Object.keys(payload)) {
    const value = parseNumeric(payload[key]);
    if (value !== null) return value;
  }

  return null;
}

function statusClass(ok, latencyMs) {
  if (!ok) return "err";
  if (latencyMs > 1400) return "warn";
  return "ok";
}

function renderRows() {
  const rows = DEFAULT_ENDPOINTS.map((ep) => {
    const result = state.results[ep.key];
    if (!result) {
      return `<div class="status-row"><span>${ep.label}</span><span class="status-badge warn">pendente</span></div>`;
    }
    const cssClass = statusClass(result.ok, result.latencyMs);
    const badge = result.ok ? `${result.latencyMs} ms` : `erro ${result.status || ""}`.trim();
    return `<div class="status-row"><span>${ep.label}</span><span class="status-badge ${cssClass}">${badge}</span></div>`;
  }).join("");

  els.endpointStatus.innerHTML = rows;
}

function renderSummary() {
  const health = state.results.health;
  els.healthValue.textContent = health?.ok ? "OK" : "ERRO";

  const filaValue = extractBestNumber(state.results.filaIn100?.payload);
  els.filaIn100Value.textContent = filaValue === null ? "--" : String(filaValue);

  const saldoKeys = ["saldoHandMais", "saldoV8", "saldoPresenca", "saldoIn100", "saldoPrata"];
  const consultaKeys = ["consultaHandMais", "consultaV8", "consultaPresenca", "consultaIn100", "consultaPrata"];

  const saldoCount = saldoKeys.filter((key) => state.results[key]?.ok).length;
  const consultaCount = consultaKeys.filter((key) => state.results[key]?.ok).length;

  els.saldosCountValue.textContent = `${saldoCount}/5`;
  els.consultasCountValue.textContent = `${consultaCount}/5`;
}

function renderPreview() {
  const firstError = Object.values(state.results).find((r) => !r.ok);
  const source = firstError || state.results.health || Object.values(state.results)[0];
  if (!source) {
    els.payloadPreview.textContent = "Sem dados ainda.";
    return;
  }
  els.payloadPreview.textContent = JSON.stringify(source.payload, null, 2);
}

async function fetchEndpoint(ep) {
  const startedAt = performance.now();
  const base = normalizeBase(state.apiBaseUrl);
  const url = `${base}${ep.path}`;
  try {
    const response = await fetch(url, { headers: { Accept: "application/json" } });
    const text = await response.text();
    let payload = text;
    try {
      payload = JSON.parse(text);
    } catch (_err) {
    }

    return {
      key: ep.key,
      ok: response.ok,
      status: response.status,
      latencyMs: Math.round(performance.now() - startedAt),
      payload
    };
  } catch (error) {
    return {
      key: ep.key,
      ok: false,
      status: 0,
      latencyMs: Math.round(performance.now() - startedAt),
      payload: { error: error.message }
    };
  }
}

async function refreshAll() {
  els.refreshButton.disabled = true;
  const jobs = DEFAULT_ENDPOINTS.map(fetchEndpoint);
  const results = await Promise.all(jobs);
  for (const result of results) {
    state.results[result.key] = result;
  }
  renderRows();
  renderSummary();
  renderPreview();
  els.lastUpdate.textContent = `Ultima atualizacao: ${formatTimestamp()}`;
  els.refreshButton.disabled = false;
}

function bindEvents() {
  els.refreshButton.addEventListener("click", refreshAll);
  els.saveApiBaseUrlButton.addEventListener("click", () => {
    state.apiBaseUrl = normalizeBase(els.apiBaseUrlInput.value);
    localStorage.setItem("europa45_api_base", state.apiBaseUrl);
    refreshAll();
  });
}

function init() {
  els.apiBaseUrlInput.value = state.apiBaseUrl;
  bindEvents();
  refreshAll();
}

init();
