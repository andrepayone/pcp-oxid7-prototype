document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('pcpinstallment');
    const radioButtons = form.querySelectorAll('input[type="radio"]');

    radioButtons.forEach(radio => {
        radio.addEventListener('click', function () {
            // Find the matching <dd> element based on the selected radio button
            const selectedDl = radio.closest('dl');
            const allDls = form.querySelectorAll('dl');

            allDls.forEach(dl => {
                const dd = dl.querySelector('dd');
                if (dl === selectedDl) {
                    dd.classList.add('activeInstallment');
                } else {
                    dd.classList.remove('activeInstallment');
                }
            });
        });
    });
});
