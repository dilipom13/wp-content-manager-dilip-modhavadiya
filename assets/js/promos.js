(function () {
  async function fetchFromAjaxHtml() {
    const form = new URLSearchParams();
    form.append("action", dcmData.action);
    form.append("nonce", dcmData.nonce);

    const res = await fetch(dcmData.ajaxUrl, {
      method: "POST",
      credentials: "same-origin",
      headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
      body: form.toString(),
    });

    if (!res.ok) throw new Error("admin-ajax failed: " + res.status);

    const json = await res.json();
    if (!json || !json.success) throw new Error("admin-ajax response not success");

    return (json.data && typeof json.data.html === "string") ? json.data.html : "";
  }

  async function loadPromos(container) {
    try {
      container.innerHTML = '<p class="dcm-loading">Loading promos...</p>';
      const html = await fetchFromAjaxHtml();
      container.innerHTML = html || "";
    } catch (e) {
      console.error("DCM promos error:", e);
      container.innerHTML = "<p>Promos temporarily unavailable.</p>";
    }
  }

  document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('[data-dcm-promos="1"]').forEach(loadPromos);
  });
})();