function getCsrf() {
    const m = document.querySelector('meta[name="csrf-token"]');
    return m ? m.content : '';
}

function favFetch(url, onSuccess) {
    fetch(url, {
        method: 'POST',
        headers: {'X-CSRF-Token': getCsrf()}
    }).then(r => r.json()).then(onSuccess);
}

// Disable empty fields before form submit to keep URL clean
document.querySelectorAll('form[data-disable-empty]').forEach(form => {
    form.addEventListener('submit', function() {
        this.querySelectorAll('select, input').forEach(el => {
            if (el.value === '') el.disabled = true;
        });
    });
});

// Single fav-ticket button (ticket detail page)
const favTicketBtn = document.getElementById('fav-ticket');
if (favTicketBtn) {
    favTicketBtn.addEventListener('click', function() {
        favFetch(this.dataset.url, d => {
            if (d.success) this.classList.toggle('active', d.active);
            else if (d.message) alert(d.message);
        });
    });
}

// Single fav-airline button (airline page)
const favAirlineBtn = document.getElementById('fav-airline');
if (favAirlineBtn) {
    favAirlineBtn.addEventListener('click', function() {
        favFetch(this.dataset.url, d => {
            if (d.success) this.classList.toggle('active', d.active);
            else if (d.message) alert(d.message);
        });
    });
}

// Multiple fav-ticket buttons (flight page)
document.querySelectorAll('.fav-ticket-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        favFetch(this.dataset.url, d => {
            if (d.success) this.classList.toggle('active', d.active);
            else if (d.message) alert(d.message);
        });
    });
});

// Favorites page: remove buttons
document.querySelectorAll('.fav-remove-airline').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        favFetch(this.dataset.url, d => {
            if (d.success) document.getElementById('fav-airline-' + id).remove();
        });
    });
});
document.querySelectorAll('.fav-remove-ticket').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        favFetch(this.dataset.url, d => {
            if (d.success) document.getElementById('fav-ticket-' + id).remove();
        });
    });
});

// Star rating buttons (airline page)
document.querySelectorAll('.star-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const val  = this.dataset.value;
        const csrf = getCsrf();
        fetch(this.dataset.url, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': csrf},
            body: '_csrf=' + csrf + '&value=' + val
        }).then(r => r.json()).then(d => {
            if (d.success) {
                document.querySelectorAll('.star-btn').forEach((b, i) => b.classList.toggle('active', i < val));
                const avgEl = document.getElementById('avg-text');
                if (avgEl) avgEl.textContent = 'Средняя: ' + d.avg;
                location.reload();
            }
        });
    });
});

// Review tab switcher (airline cabinet)
function switchReviews(type) {
    const active  = document.getElementById('reviews-active');
    const deleted = document.getElementById('reviews-deleted');
    const btnA    = document.getElementById('btn-show-active');
    const btnD    = document.getElementById('btn-show-deleted');
    if (!active) return;
    active.style.display  = type === 'active'  ? '' : 'none';
    deleted.style.display = type === 'deleted' ? '' : 'none';
    btnA.className = 'btn btn-sm ' + (type === 'active'  ? 'btn-primary' : 'btn-outline-secondary');
    btnD.className = 'btn btn-sm ' + (type === 'deleted' ? 'btn-primary' : 'btn-outline-secondary');
}

// Review tab switcher (admin airline view)
function admSwitchReviews(type) {
    const active  = document.getElementById('adm-reviews-active');
    const deleted = document.getElementById('adm-reviews-deleted');
    const btnA    = document.getElementById('adm-btn-active');
    const btnD    = document.getElementById('adm-btn-deleted');
    if (!active) return;
    active.style.display  = type === 'active'  ? '' : 'none';
    deleted.style.display = type === 'deleted' ? '' : 'none';
    btnA.className = 'btn btn-sm ' + (type === 'active'  ? 'btn-primary' : 'btn-outline-secondary');
    btnD.className = 'btn btn-sm ' + (type === 'deleted' ? 'btn-primary' : 'btn-outline-secondary');
}
