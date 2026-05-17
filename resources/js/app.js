import './bootstrap';

const maxFields = 3;
const container = document.getElementById('program-container');
const addButton = document.getElementById('add-program');

if (container && addButton) {
    addButton.addEventListener('click', function() {
        const currentFields = container.getElementsByClassName('program-item').length;

        if (currentFields < maxFields) {
            const newRow = document.createElement('div');
            newRow.className = 'flex items-center gap-2 program-item';

            newRow.innerHTML = `
                <select name="job_programs[]" class="w-full p-2 border rounded">
                    {!! $programOptions !!}
                </select>
                <button type="button" class="remove-program bg-red-500 text-white px-4 py-2 rounded">
                    <i class="fa-solid fa-minus"></i>
                </button>
            `;

            container.appendChild(newRow);

            if (container.getElementsByClassName('program-item').length === maxFields) {
                addButton.style.display = 'none';
            }
        }
    });
}