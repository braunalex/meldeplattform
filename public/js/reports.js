(function () {
    const closedCb = document.getElementById('hide-closed');
    const spamCb = document.getElementById('hide-spam');
    const rows = document.querySelectorAll('.report-row');

    function apply() {
        rows.forEach((r) => {
            const closed = r.dataset.closed === '1';
            const spam = r.dataset.spam === '1';
            const hide = (closedCb.checked && closed) || (spamCb.checked && spam);
            r.style.display = hide ? 'none' : '';
        });
    }

    closedCb.addEventListener('change', apply);
    spamCb.addEventListener('change', apply);
    apply();
})();
