{{--
    Shared "View Details" modal for job cards — used by the job board
    (partials.job-post-card) and the alumni dashboard's "Job Matches For
    You" cards, so clicking either kind of card opens the identical modal.
    openJobModal() reads everything from the triggering element's data-*
    attributes, so any clickable element (button, div w/ role="button")
    with the right data-* set can open it.
--}}
<div id="jobModal" class="fixed inset-0 z-[100] hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">

    <div class="bg-white w-full max-w-3xl rounded-3xl shadow-2xl relative">

        <div class="h-48 w-full relative rounded-t-3xl overflow-hidden">
            <img id="modal-img" src="https://images.unsplash.com/photo-1521737711867-e3b97375f902?auto=format&fit=crop&q=80&w=800" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-blue-900/40 mix-blend-multiply"></div>

            <button onclick="toggleModal()" class="absolute top-4 right-4 bg-white/20 hover:bg-white/40 text-white rounded-full p-1 transition-colors">
                <i class="fas fa-times-circle text-2xl"></i>
            </button>
        </div>

        <div class="p-8">
            <div class="flex justify-between items-start">
                <div>
                    <h2 id="modal-title" class="text-3xl font-bold text-[#1D264F] uppercase tracking-tighter">JOB TITLE</h2>
                    <div class="flex items-center text-gray-600 mt-1 space-x-4">
                        <p id="modal-company" class="font-semibold text-lg">Company Name</p>
                        <span id="modal-date" class="flex items-center text-sm"><i class="far fa-calendar-alt mr-2"></i> Posted 2 days ago</span>
                    </div>
                    <p id="modal-address" class="text-gray-500 font-medium">Company Address</p>
                </div>

                <div class="relative">
                    <div id="modal-share-tooltip" class="invisible opacity-0 absolute bottom-full right-0 mb-2 pointer-events-none transition-all duration-300 z-50">
                        <div class="bg-gray-800 text-white text-[10px] py-1 px-3 rounded shadow-xl whitespace-nowrap relative">
                            Link Copied!
                            <div class="absolute top-full right-2 w-0 h-0 border-l-[6px] border-l-transparent border-r-[6px] border-r-transparent border-t-[6px] border-t-gray-800"></div>
                        </div>
                    </div>
                    <button onclick="showModalTooltip('modal-share-tooltip')" class="text-[#1D264F] text-2xl hover:scale-110 transition-transform">
                        <i class="fas fa-share-nodes"></i>
                    </button>
                </div>
            </div>

            <div class="mt-8 flex flex-col md:flex-row gap-8">
                <div class="md:w-3/5">
                    <h3 class="font-bold text-[#0E0F3B] mb-3">Job Description:</h3>
                    <div id="modal-description" class="text-gray-600 text-sm leading-relaxed text-justify job-description-content">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent placerat, nulla quis aliquam fringilla, nulla elit accumsan nisi, vel feugiat massa erat vel eros. Curabitur sed massa vel leo accumsan imperdiet.
                    </div>
                </div>

                <div class="md:w-2/5 space-y-2 text-[#1D264F]">
                    <p class="flex justify-between text-sm"><span class="font-bold">Job Type:</span> <span id="modal-job-type">Full-Time</span></p>
                    <p class="flex justify-between text-sm"><span class="font-bold">Job Setup:</span> <span id="modal-job-setup">Remote</span></p>
                </div>
            </div>

            <div class="pt-2 border-t border-gray-100">
                <p class="font-bold text-sm">Recommended Course/Program:</p>
                <p id="modal-programs" class="text-sm leading-snug text-gray-600 mt-1">
                    BSIT - Bachelor of Science in Information Technology
                </p>
            </div>

            <div class="pt-2 border-t border-gray-100">
                <p class="font-bold text-sm">Industry / Sector:</p>
                <p id="modal-industry" class="text-sm leading-snug text-gray-600 mt-1"></p>
            </div>

            <div class="mt-10 pt-6 border-t flex items-center justify-between">
                <div id="modal-valid" class="text-gray-500 text-sm flex items-center font-semibold">
                    <i class="far fa-calendar-check mr-2"></i> Valid until
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openJobModal(btn) {
        document.getElementById('modal-img').src = btn.dataset.image;
        document.getElementById('modal-title').textContent = btn.dataset.title;
        document.getElementById('modal-company').textContent = btn.dataset.company;
        document.getElementById('modal-date').innerHTML = '<i class="far fa-calendar-alt mr-2"></i> ' + btn.dataset.date;
        document.getElementById('modal-address').textContent = btn.dataset.address;
        document.getElementById('modal-description').innerHTML = btn.dataset.description;
        document.getElementById('modal-job-type').textContent = btn.dataset.type;
        document.getElementById('modal-job-setup').textContent = btn.dataset.setup;
        document.getElementById('modal-programs').textContent = btn.dataset.programs;
        document.getElementById('modal-industry').textContent = btn.dataset.industry;
        document.getElementById('modal-valid').innerHTML = '<i class="far fa-calendar-check mr-2"></i> Valid until: ' + btn.dataset.valid;

        document.getElementById('jobModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function toggleModal() {
        const modal = document.getElementById('jobModal');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Optional: Close modal if user clicks outside the white box
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('jobModal');
        if (event.target == modal) {
            toggleModal();
        }
    });

    function showModalTooltip(tooltipId) {
        const tooltip = document.getElementById(tooltipId);

        tooltip.classList.remove('invisible', 'opacity-0');
        tooltip.classList.add('opacity-100');

        if (tooltipId === 'modal-share-tooltip') {
            navigator.clipboard.writeText(window.location.href);
        }

        setTimeout(() => {
            tooltip.classList.add('invisible', 'opacity-0');
            tooltip.classList.remove('opacity-100');
        }, 2000);
    }
</script>
