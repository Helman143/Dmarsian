document.addEventListener('DOMContentLoaded', function() {
    const editBtn = document.getElementById('editProfileBtn');
    const saveBtn = document.getElementById('saveProfileBtn');
    const cancelBtn = document.getElementById('cancelEditBtn');
    const inputs = [
        document.getElementById('email'),
        document.getElementById('username'),
        document.getElementById('password')
    ];
    let originalValues = inputs.map(input => input.value);

    // #region agent log - overlap diagnostics
    try {
        const welcomeHeader = document.querySelector('.welcome-header');
        const h1 = welcomeHeader ? welcomeHeader.querySelector('h1') : null;
        if (welcomeHeader) {
            const rect = welcomeHeader.getBoundingClientRect();
            const centerX = rect.left + rect.width / 2;
            const centerY = rect.top + rect.height / 2;
            const topEl = document.elementFromPoint(centerX, centerY);
            const topRect = topEl ? topEl.getBoundingClientRect() : null;
            const topStyles = topEl ? window.getComputedStyle(topEl) : null;
            const headerStyles = window.getComputedStyle(welcomeHeader);
            const mobileTopbar = document.querySelector('.mobile-topbar');
            fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    sessionId: 'debug-session',
                    runId: 'pre-fix',
                    hypothesisId: 'H-overlap-zindex-or-offset',
                    location: 'Scripts/admin_profile.js:overlap-probe',
                    message: 'Admin Profile welcome-header overlap probe',
                    data: {
                        headerRect: rect,
                        headerZ: headerStyles.zIndex,
                        headerPosition: headerStyles.position,
                        headerPaddingTop: headerStyles.paddingTop,
                        topElementTag: topEl ? topEl.tagName : null,
                        topElementClass: topEl ? topEl.className : null,
                        topElementZ: topStyles ? topStyles.zIndex : null,
                        topElementPosition: topStyles ? topStyles.position : null,
                        topElementRect: topRect,
                        hasMobileTopbar: !!mobileTopbar,
                        mobileTopbarZ: mobileTopbar ? window.getComputedStyle(mobileTopbar).zIndex : null
                    },
                    timestamp: Date.now()
                })
            }).catch(() => {});
        }
    } catch (e) {
        fetch('http://127.0.0.1:7246/ingest/172589e8-eef2-4849-afba-712c85ef0ddf', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                sessionId: 'debug-session',
                runId: 'pre-fix',
                hypothesisId: 'H-overlap-exception',
                location: 'Scripts/admin_profile.js:overlap-probe',
                message: 'Error while probing overlap',
                data: { error: String(e && e.message || e) },
                timestamp: Date.now()
            })
        }).catch(() => {});
    }
    // #endregion agent log - overlap diagnostics

    editBtn.addEventListener('click', function() {
        inputs.forEach(input => input.disabled = false);
        editBtn.style.display = 'none';
        saveBtn.style.display = 'inline-block';
        cancelBtn.style.display = 'inline-block';
    });

    cancelBtn.addEventListener('click', function() {
        inputs.forEach((input, i) => {
            input.value = originalValues[i];
            input.disabled = true;
        });
        editBtn.style.display = 'inline-block';
        saveBtn.style.display = 'none';
        cancelBtn.style.display = 'none';
    });

    document.getElementById('profileForm').addEventListener('submit', function(e) {
        // Allow default form submission to server for PHP handling
        // originalValues = inputs.map(input => input.value);
        // inputs.forEach(input => input.disabled = true);
        // editBtn.style.display = 'inline-block';
        // saveBtn.style.display = 'none';
        // cancelBtn.style.display = 'none';
        // alert('Profile updated! (Demo only, not saved to server)');
    });
}); 