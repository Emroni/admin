// Confirms
document.querySelectorAll('[data-confirm]').forEach(element => {
    element.addEventListener('click', handleConfirm);
    element.addEventListener('auxclick', handleConfirm);
});

function handleConfirm(e) {
    const text = e.currentTarget.getAttribute('data-confirm');
    if (!window.confirm(text)) {
        e.preventDefault();
    }
}

// Links
document.querySelectorAll('[data-link]').forEach(element => {
    element.addEventListener('click', handleLink);
    element.addEventListener('auxclick', handleLink);
});

function handleLink(e) {
    if (!e.target.getAttribute('href')) {
        const link = e.currentTarget.getAttribute('data-link');
        if (e.ctrlKey || e.metaKey || e.type === 'auxclick') {
            window.open(link);
        } else {
            window.location = link;
        }
    }
}