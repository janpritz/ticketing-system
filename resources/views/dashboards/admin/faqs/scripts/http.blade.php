async function apiFetch(url, method = 'GET', body = null) {
    const headers = { 'X-Requested-With': 'XMLHttpRequest' };
    if (method !== 'GET') {
        headers['Content-Type'] = 'application/json';
        headers['X-CSRF-TOKEN'] = Config.csrf;
    }

    const options = { method, headers };
    if (body) options.body = JSON.stringify(body);

    const response = await fetch(url, options);
    if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);
    return await response.json();
}