document.addEventListener('DOMContentLoaded', () => {
    // ✅ Save button logic
    const saveButton = document.getElementById('save-changes');
    const updateUrl = document.querySelector('.payment-container')?.dataset.updateUrl;

    if (saveButton && updateUrl) {
        saveButton.addEventListener('click', () => {
            const changes = {};

            document.querySelectorAll('tr[data-member-id]').forEach(row => {
                const memberId = row.dataset.memberId;
                const yearData = {};

                row.querySelectorAll('.payment-input').forEach(input => {
                    const year = input.dataset.year;
                    const amount = parseFloat(input.value) || 0;
                    yearData[year] = amount;
                });

                changes[memberId] = yearData;
            });

            fetch(updateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(changes)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Modifications enregistrées avec succès.');
                    window.location.reload();
                } else {
                    alert('Erreur lors de l’enregistrement : ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur s’est produite lors de l’enregistrement.');
            });
        });
    }

    // ✅ Edit modal logic
    document.querySelectorAll('.edit-icon').forEach(icon => {
        icon.addEventListener('click', function () {
            const row = this.closest('tr');
            const memberId = row.dataset.memberId;

            // Populate modal fields
            document.getElementById('edit-member-id').value = memberId;
            document.getElementById('edit-name').value = row.querySelector('td:nth-child(3)').innerText;
            document.getElementById('edit-email').value = row.querySelector('td:nth-child(4)').innerText;
            document.getElementById('edit-city').value = row.querySelector('td:nth-child(5)').innerText;
            document.getElementById('edit-status').value = row.querySelector('td:nth-child(6)').innerText;
            document.getElementById('edit-promotion-year').value = row.querySelector('td:nth-child(7)').innerText;

            row.querySelectorAll('.payment-input').forEach(input => {
                const year = input.dataset.year;
                document.querySelector(`.annual-payment-input[data-year="${year}"]`).value = input.value;
            });

            document.getElementById('edit-dues-paid').checked = row.querySelector('.badge-success') !== null;

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('editMemberModal'));
            modal.show();
        });
    });

    document.getElementById('edit-member-form')?.addEventListener('submit', function (e) {
        e.preventDefault();
        const memberId = document.getElementById('edit-member-id').value;

        const payload = {
            name: document.getElementById('edit-name').value,
            email: document.getElementById('edit-email').value,
            city: document.getElementById('edit-city').value,
            status: document.getElementById('edit-status').value,
            promotionYear: parseInt(document.getElementById('edit-promotion-year').value),
            duesPaid: document.getElementById('edit-dues-paid').checked,
            annualPayments: {},
        };

        document.querySelectorAll('.annual-payment-input').forEach(input => {
            const year = input.dataset.year;
            payload.annualPayments[year] = parseFloat(input.value) || 0;
        });

        fetch(`/member/${memberId}/edit-json`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Membre mis à jour avec succès.');
                window.location.reload();
            } else {
                alert('Erreur: ' + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('Une erreur est survenue.');
        });
    });

    // ✅ Generate all codes logic
    const generateBtn = document.getElementById('generate-all-codes');
    if (generateBtn) {
        generateBtn.addEventListener('click', function () {
            if (!confirm("Voulez-vous vraiment régénérer les codes pour tous les membres ?")) {
                return;
            }

            fetch(generateBtn.dataset.url, { // 💡 Pass URL as data attribute
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(() => {
                alert("Une erreur s'est produite lors de la génération des codes.");
            });
        });
    }
});
