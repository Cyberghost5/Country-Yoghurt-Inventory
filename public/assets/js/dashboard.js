const moduleData = {
  admin: {
    title: "Admin Dashboard",
    subtitle: "Thursday, 30 April 2026 · Bauchi HQ",
    user: { initials: "AM", name: "Admin User", email: "admin@countryyoghurt.ng" },
    chartMeta: "Inbound vs Outbound · Apr 24 - Apr 30",
    tableMeta: "Last 7 stock movements",
    alertMeta: "4 products need reorder",
    chartPoints: [340, 305, 455, 410, 500, 180, 240],
    kpis: [
      { value: "2,450", unit: "units", label: "Total Stock", trend: "+8.2%", tone: "success", icon: "bi-box-seam" },
      { value: "18", unit: "orders", label: "Pending Orders", trend: "+3 today", tone: "warn", icon: "bi-cart3" },
      { value: "7", unit: "in transit", label: "Active Deliveries", trend: "On schedule", tone: "info", icon: "bi-truck" },
      { value: "4", unit: "products", label: "Low Stock Alerts", trend: "Needs reorder", tone: "danger", icon: "bi-exclamation-triangle" }
    ],
    alerts: [
      { name: "Full Cream Yoghurt 2L", stock: "14 left", threshold: "28% of threshold (50 units)", percent: 28 },
      { name: "Honey & Ginger 250g", stock: "22 left", threshold: "37% of threshold (60 units)", percent: 37 },
      { name: "Mango Yoghurt 1L", stock: "8 left", threshold: "20% of threshold (40 units)", percent: 20 },
      { name: "Strawberry 500g", stock: "31 left", threshold: "39% of threshold (80 units)", percent: 39 }
    ],
    transactions: [
      { id: "TXN-00421", product: "Classic Plain Yoghurt 500g", type: "IN", quantity: "240 Units", party: "Bauchi Dairy Co.", date: "30 Apr 2026", status: "Completed" },
      { id: "TXN-00420", product: "Honey & Ginger Yoghurt 250g", type: "OUT", quantity: "80 Units", party: "-", date: "30 Apr 2026", status: "Completed" },
      { id: "TXN-00419", product: "Strawberry Yoghurt 1L", type: "IN", quantity: "120 Units", party: "Jos Fresh Farms", date: "29 Apr 2026", status: "Pending" },
      { id: "TXN-00418", product: "Mango Yoghurt 500g", type: "OUT", quantity: "60 Units", party: "-", date: "29 Apr 2026", status: "Completed" },
      { id: "TXN-00417", product: "Full Cream Yoghurt 2L", type: "IN", quantity: "48 Units", party: "Plateau Milk Works", date: "28 Apr 2026", status: "Alert" },
      { id: "TXN-00416", product: "Classic Plain Yoghurt 250g", type: "OUT", quantity: "200 Units", party: "-", date: "28 Apr 2026", status: "Completed" },
      { id: "TXN-00415", product: "Banana Yoghurt 500g", type: "IN", quantity: "96 Units", party: "Bauchi Dairy Co.", date: "27 Apr 2026", status: "Pending" }
    ]
  },
  staff: {
    title: "Staff Dashboard",
    subtitle: "Thursday, 30 April 2026 · Operations Floor",
    user: { initials: "ST", name: "Staff Supervisor", email: "staff@countryyoghurt.ng" },
    chartMeta: "Stock handling throughput · Apr 24 - Apr 30",
    tableMeta: "Last 7 handling transactions",
    alertMeta: "3 products need urgent restock",
    chartPoints: [290, 325, 410, 365, 445, 210, 260],
    kpis: [
      { value: "1,980", unit: "units", label: "Warehouse Stock", trend: "+5.1%", tone: "success", icon: "bi-box-seam" },
      { value: "23", unit: "entries", label: "Stock Entries Today", trend: "+7 today", tone: "warn", icon: "bi-journal-plus" },
      { value: "9", unit: "routes", label: "Dispatch Runs", trend: "On schedule", tone: "info", icon: "bi-signpost-split" },
      { value: "3", unit: "products", label: "Critical Alerts", trend: "Needs reorder", tone: "danger", icon: "bi-exclamation-octagon" }
    ],
    alerts: [
      { name: "Classic Plain 250g", stock: "10 left", threshold: "25% of threshold (40 units)", percent: 25 },
      { name: "Vanilla Family Pack", stock: "13 left", threshold: "29% of threshold (45 units)", percent: 29 },
      { name: "Mango 500g", stock: "9 left", threshold: "22% of threshold (40 units)", percent: 22 }
    ],
    transactions: [
      { id: "TXN-00501", product: "Classic Plain 250g", type: "IN", quantity: "160 Units", party: "Bauchi Dairy Co.", date: "30 Apr 2026", status: "Completed" },
      { id: "TXN-00500", product: "Vanilla Family Pack", type: "OUT", quantity: "40 Units", party: "Retail Route A", date: "30 Apr 2026", status: "Completed" },
      { id: "TXN-00499", product: "Mango Yoghurt 500g", type: "OUT", quantity: "66 Units", party: "Retail Route C", date: "29 Apr 2026", status: "Pending" },
      { id: "TXN-00498", product: "Honey & Ginger 250g", type: "IN", quantity: "70 Units", party: "Jos Fresh Farms", date: "29 Apr 2026", status: "Completed" },
      { id: "TXN-00497", product: "Strawberry Yoghurt 1L", type: "OUT", quantity: "58 Units", party: "Retail Route B", date: "28 Apr 2026", status: "Alert" },
      { id: "TXN-00496", product: "Banana Yoghurt 500g", type: "IN", quantity: "88 Units", party: "Plateau Milk Works", date: "28 Apr 2026", status: "Completed" }
    ]
  },
  customer: {
    title: "Customer Dashboard",
    subtitle: "Thursday, 30 April 2026 · Distribution Network",
    user: { initials: "CU", name: "Customer Desk", email: "customer@countryyoghurt.ng" },
    chartMeta: "Order fulfillment trend · Apr 24 - Apr 30",
    tableMeta: "Last 7 customer-facing movements",
    alertMeta: "2 products need stock attention",
    chartPoints: [260, 280, 390, 430, 470, 240, 285],
    kpis: [
      { value: "1,340", unit: "accounts", label: "Active Customers", trend: "+4.6%", tone: "success", icon: "bi-people" },
      { value: "66", unit: "orders", label: "Orders Today", trend: "+9 today", tone: "warn", icon: "bi-bag" },
      { value: "91%", unit: "on-time", label: "Delivery Success", trend: "On schedule", tone: "info", icon: "bi-clock-history" },
      { value: "2", unit: "products", label: "Low Stock Risks", trend: "Needs reorder", tone: "danger", icon: "bi-exclamation-triangle" }
    ],
    alerts: [
      { name: "Classic Plain 500g", stock: "16 left", threshold: "32% of threshold (50 units)", percent: 32 },
      { name: "Strawberry 1L", stock: "11 left", threshold: "24% of threshold (45 units)", percent: 24 }
    ],
    transactions: [
      { id: "TXN-00610", product: "Classic Plain 500g", type: "OUT", quantity: "120 Units", party: "Kano Supermart", date: "30 Apr 2026", status: "Completed" },
      { id: "TXN-00609", product: "Strawberry Yoghurt 1L", type: "OUT", quantity: "56 Units", party: "Jos Plaza", date: "30 Apr 2026", status: "Pending" },
      { id: "TXN-00608", product: "Honey & Ginger 250g", type: "IN", quantity: "90 Units", party: "Bauchi Dairy Co.", date: "29 Apr 2026", status: "Completed" },
      { id: "TXN-00607", product: "Mango Yoghurt 500g", type: "OUT", quantity: "44 Units", party: "Bukuru Stores", date: "29 Apr 2026", status: "Completed" },
      { id: "TXN-00606", product: "Full Cream Yoghurt 2L", type: "OUT", quantity: "33 Units", party: "Ningi Wholesales", date: "28 Apr 2026", status: "Alert" },
      { id: "TXN-00605", product: "Banana Yoghurt 500g", type: "IN", quantity: "72 Units", party: "Plateau Milk Works", date: "28 Apr 2026", status: "Completed" }
    ]
  }
};

const pageTitle = document.getElementById("pageTitle");
const pageSubtitle = document.getElementById("pageSubtitle");
const chartMeta = document.getElementById("chartMeta");
const tableMeta = document.getElementById("tableMeta");
const alertMeta = document.getElementById("alertMeta");
const kpiGrid = document.getElementById("kpiGrid");
const transactionTable = document.getElementById("transactionTable");
const alertList = document.getElementById("alertList");
const stockLine = document.getElementById("stockLine");
const stockArea = document.getElementById("stockArea");

const kpiTemplate = document.getElementById("kpiTemplate");
const transactionTemplate = document.getElementById("transactionTemplate");
const alertTemplate = document.getElementById("alertTemplate");

function drawLine(points) {
  const min = Math.min(...points);
  const max = Math.max(...points);
  const chartWidth = 615;
  const chartHeight = 176;
  const startX = 35;
  const startY = 38;
  const stepX = chartWidth / (points.length - 1);

  const normalized = points.map((point) => {
    const scaled = (point - min) / Math.max(1, max - min);
    return startY + chartHeight - scaled * chartHeight;
  });

  const lineCommands = points
    .map((_, index) => {
      const x = startX + index * stepX;
      const y = normalized[index];
      return `${index === 0 ? "M" : "L"} ${x.toFixed(2)} ${y.toFixed(2)}`;
    })
    .join(" ");

  const areaCommands = `${lineCommands} L ${startX + chartWidth} ${startY + chartHeight} L ${startX} ${startY + chartHeight} Z`;

  stockLine.setAttribute("d", lineCommands);
  stockArea.setAttribute("d", areaCommands);
}

function renderKpis(kpis) {
  kpiGrid.innerHTML = "";

  kpis.forEach((item) => {
    const node = kpiTemplate.content.cloneNode(true);
    const root = node.querySelector(".stat-card");
    const trend = node.querySelector(".trend-pill");
    const icon = node.querySelector(".mini-icon-symbol");

    if (item.icon) {
      icon.classList.add(item.icon);
    }

    if (item.tone === "warn") {
      root.classList.add("warn");
    }
    if (item.tone === "info") {
      root.classList.add("info");
    }
    if (item.tone === "danger") {
      root.classList.add("danger");
      trend.classList.add("danger");
    }

    node.querySelector(".stat-value").textContent = item.value;
    node.querySelector(".stat-unit").textContent = item.unit;
    node.querySelector(".stat-label").textContent = item.label;
    trend.textContent = item.trend;

    kpiGrid.appendChild(node);
  });
}

function renderAlerts(alerts) {
  alertList.innerHTML = "";

  alerts.forEach((item) => {
    const node = alertTemplate.content.cloneNode(true);
    node.querySelector(".alert-name").textContent = item.name;
    node.querySelector(".alert-stock").textContent = item.stock;
    node.querySelector(".alert-threshold").textContent = item.threshold;
    node.querySelector(".alert-fill").style.width = `${item.percent}%`;
    alertList.appendChild(node);
  });
}

function renderTransactions(rows) {
  transactionTable.innerHTML = "";

  rows.forEach((row) => {
    const node = transactionTemplate.content.cloneNode(true);
    const typeNode = node.querySelector(".tx-type");
    const statusNode = node.querySelector(".tx-status");

    node.querySelector(".tx-id").textContent = row.id;
    node.querySelector(".tx-product").textContent = row.product;
    node.querySelector(".tx-qty").textContent = row.quantity;
    node.querySelector(".tx-party").textContent = row.party;
    node.querySelector(".tx-date").textContent = row.date;

    typeNode.textContent = row.type;
    typeNode.classList.add(row.type.toLowerCase());

    statusNode.textContent = row.status;
    statusNode.classList.add(row.status.toLowerCase());

    transactionTable.appendChild(node);
  });
}

function renderModule(moduleKey) {
  // Guard: if the dashboard DOM isn't present (e.g. reports page), do nothing.
  if (!pageTitle || !kpiGrid || !transactionTable) {
    return;
  }

  const allowedModules = Array.isArray(window.CY_ALLOWED_MODULES)
    ? window.CY_ALLOWED_MODULES
    : [];

  if (allowedModules.length && !allowedModules.includes(moduleKey)) {
    return;
  }

  const data = moduleData[moduleKey];
  if (!data) {
    return;
  }

  pageTitle.textContent = data.title;
  pageSubtitle.textContent = data.subtitle;
  chartMeta.textContent = data.chartMeta;
  tableMeta.textContent = data.tableMeta;
  alertMeta.textContent = data.alertMeta;

  renderKpis(data.kpis);
  renderAlerts(data.alerts);
  renderTransactions(data.transactions);
  drawLine(data.chartPoints);
}

document.querySelectorAll(".module-tab").forEach((tab) => {
  tab.addEventListener("click", () => {
    document.querySelectorAll(".module-tab").forEach((item) => item.classList.remove("active"));
    tab.classList.add("active");
    renderModule(tab.dataset.module);
  });
});

const initialModule = window.CY_USER_ROLE && moduleData[window.CY_USER_ROLE]
  ? window.CY_USER_ROLE
  : "admin";
renderModule(initialModule);

/* ── Sidebar toggle (mobile) ── */
(function () {
  var sidebar = document.getElementById("sidebar");
  var backdrop = document.getElementById("sidebarBackdrop");
  var toggle = document.getElementById("sidebarToggle");
  var close = document.getElementById("sidebarClose");
  function openSidebar() {
    sidebar.classList.add("is-open");
    backdrop.classList.add("is-open");
    document.body.style.overflow = "hidden";
  }
  function closeSidebar() {
    sidebar.classList.remove("is-open");
    backdrop.classList.remove("is-open");
    document.body.style.overflow = "";
  }
  if (toggle) toggle.addEventListener("click", openSidebar);
  if (close) close.addEventListener("click", closeSidebar);
  if (backdrop) backdrop.addEventListener("click", closeSidebar);
})();
