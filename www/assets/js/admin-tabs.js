document.addEventListener('DOMContentLoaded', function() {

    const postBtn = document.getElementById('postBtn');
    const commentBtn = document.getElementById('commentBtn');
    const userBtn = document.getElementById('userBtn');

    const postSection = document.getElementById('postSection');
    const commentSection = document.getElementById('commentSection');
    const userSection = document.getElementById('userSection');

    if (postBtn && commentBtn && userBtn && postSection && commentSection && userSection) {

        function switchTab(activeBtn, showSection) {
            postBtn.classList.remove('active');
            commentBtn.classList.remove('active');
            userBtn.classList.remove('active');

            postSection.classList.add('admin-hide');
            commentSection.classList.add('admin-hide');
            userSection.classList.add('admin-hide');

            activeBtn.classList.add('active');
            showSection.classList.remove('admin-hide');
        }

        postBtn.addEventListener('click', function() {
            switchTab(postBtn, postSection);
        });

        commentBtn.addEventListener('click', function() {
            switchTab(commentBtn, commentSection);
        });

        userBtn.addEventListener('click', function() {
            switchTab(userBtn, userSection);
        });
    }

    document.querySelectorAll('.admin-role-select').forEach(select => {
        select.addEventListener('change', function() {
            const newRole = this.value;
            const rawLink = this.getAttribute('data-link');

            const url = rawLink.replace('placeholder', newRole);

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert('Chyba: ' + data.error);
                        window.location.reload();
                    } else {
                        console.log('Role úspěšně změněna na:', newRole);

                        const statusSpan = document.querySelector('.admin-status');
                        if (statusSpan) {
                            const originalText = statusSpan.innerText;
                            statusSpan.innerText = 'Role uložena!';
                            statusSpan.style.color = '#10b981';
                            statusSpan.style.fontWeight = 'bold';

                            setTimeout(() => {
                                statusSpan.innerText = originalText;
                                statusSpan.style.color = '';
                                statusSpan.style.fontWeight = '';
                            }, 3000);
                        }

                        const topBadge = document.querySelector('.profile-badge');
                        if (topBadge) {
                            topBadge.innerHTML = '<i class="icon-role"></i> ' + newRole.toUpperCase();

                            if (newRole === 'admin') {
                                topBadge.classList.remove('badge-user');
                                topBadge.classList.add('badge-admin');
                            } else {
                                topBadge.classList.remove('badge-admin');
                                topBadge.classList.add('badge-user');
                            }
                        }

                        const parentTd = this.closest('td');
                        if (parentTd) {
                            const originalBg = parentTd.style.backgroundColor;
                            parentTd.style.backgroundColor = '#d1fae5'; // Světle zelená
                            parentTd.style.transition = 'background-color 0.5s ease';

                            setTimeout(() => {
                                parentTd.style.backgroundColor = originalBg;
                            }, 1000);
                        }
                    }
                })
                .catch(err => {
                    console.error('AJAX chyba:', err);
                    alert('Něco se pokazilo při komunikaci se serverem.');
                });
        });
    });

});

document.addEventListener('DOMContentLoaded', function() {
    // ... tvůj stávající kód pro přepínání záložek a změnu rolí ...

        document.querySelectorAll('.admin-vip-select').forEach(select => {
            select.addEventListener('change', function() {
                const action = this.value;
                if (!action) return;

                const userId = this.getAttribute('data-user-id');
                const rawLink = this.getAttribute('data-link');
                const url = rawLink.replace('placeholder', action);

                // Resetujeme select zpět na výchozí text "Upravit VIP..."
                this.value = "";

                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            alert('Chyba: ' + data.error);
                        } else if (data.success) {
                            console.log('VIP úspěšně změněno. Nový stav:', data.newDate);

                            // 1. NAJDEME TEXTOVÉ POLE PRO VIP A PŘEPÍŠEME HO
                            const statusDiv = document.getElementById('vip-status-text-' + userId);
                            if (statusDiv) {
                                if (data.newDate === 'Bez VIP') {
                                    statusDiv.innerHTML = '<span style="color: #94a3b8; font-size: 0.85rem;">Bez VIP</span>';
                                } else {
                                    statusDiv.innerHTML = '<span style="color: #d97706; font-weight: bold; font-size: 0.85rem;"><i class="fa-solid fa-crown"></i> ' + data.newDate + '</span>';
                                }
                            }

                            // 2. PROBLIKNUTÍ CELÉ BUŇKY ZELENOU BARVOU (vizuální potvrzení)
                            const parentTd = this.closest('td');
                            if (parentTd) {
                                const originalBg = parentTd.style.backgroundColor;
                                parentTd.style.backgroundColor = '#fef3c7'; // Jemná zlatožlutá barva pro VIP
                                parentTd.style.transition = 'background-color 0.4s ease';

                                setTimeout(() => {
                                    parentTd.style.backgroundColor = originalBg;
                                }, 800);
                            }
                        }
                    })
                    .catch(err => {
                        console.error('AJAX chyba u VIP:', err);
                        alert('Něco se pokazilo při komunikaci se serverem. Zkuste obnovit stránku.');
                    });
            });
        });
});