// public/js/app.js
// Gestion globale : helper fetch sécurisé + gestion des erreurs réseau communes.
// Aucune bibliothèque tierce (JQuery interdit) - API native fetch() uniquement.

/**
 * Wrapper autour de fetch() qui gère uniformément les erreurs réseau et HTTP.
 * Si l'API renvoie 401/403/500, affiche une alerte claire au lieu de planter
 * silencieusement dans la console (critère de performance Part 2).
 *
 * @param {string} url
 * @param {RequestInit} options
 * @returns {Promise<{ok: boolean, status: number, data: any}>}
 */
async function apiFetch(url, options = {}) {
    try {
        const response = await fetch(url, options);
        let data;

        try {
            data = await response.json();
        } catch (_) {
            data = null;
        }

        if (!response.ok) {
            const message = (data && data.error) || `Erreur ${response.status}`;
            showGlobalAlert(buildErrorMessage(response.status, message));
        }

        return { ok: response.ok, status: response.status, data };
    } catch (networkError) {
        showGlobalAlert("Impossible de contacter le serveur. Vérifiez votre connexion.");
        return { ok: false, status: 0, data: null };
    }
}

function buildErrorMessage(status, message) {
    if (status === 401) {
        return "Session expirée. Merci de vous reconnecter.";
    }
    if (status === 403) {
        return "Accès refusé : " + message;
    }
    if (status >= 500) {
        return "Erreur serveur : " + message;
    }
    return message;
}

/**
 * Affiche une alerte claire dans une zone dédiée de la page (#api-error),
 * ou en repli un une alerte navigateur si la zone n'existe pas.
 */
function showGlobalAlert(message) {
    const box = document.getElementById('api-error');
    if (box) {
        box.innerHTML = `<div class="alert-error">${message}</div>`;
        setTimeout(() => { box.innerHTML = ''; }, 6000);
    } else {
        console.error(message);
        alert(message);
    }
}

// Confirmation avant déconnexion (UX simple, pas de blocage si refusé)
document.addEventListener('DOMContentLoaded', () => {
    const logoutLink = document.querySelector('a.logout[href*="route=logout"]');
    if (logoutLink) {
        logoutLink.addEventListener('click', (e) => {
            if (!confirm('Voulez-vous vous déconnecter ?')) {
                e.preventDefault();
            }
        });
    }
});
