<!--<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
</head>
<body>
    <div>
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
    <h1>Forgot Password</h1>
    <form method="POST" action="{{ route('passReset.forgetPasswordPost') }}">
        @csrf
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        <button type="submit">Send Reset Link</button>
    </form>

</body>
</html>-->

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLV-AlumNet | Forgot Password</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&family=Poppins:wght@300;400;600;700&family=Inter:wght@400;500;700&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="assets/PLV-AlumNet LOGO.png">
</head>

<style>
    body {
        background-image:
            url("../assets/loginBackground.svg");
        background-size: cover;
        background-position: center;

        font-family: 'Montserrat', sans-serif;

        .hidden-modal {
            display: none !important;
        }
    }
</style>



<body class="min-h-screen flex items-center justify-center font-[Montserrat]">

    <div class="absolute inset-0 z-0">
        <img src="assets/alumnetBackground.svg" alt="PLV Building" class="w-full h-full object-cover">
    </div>

    <div class="relative z-10 container mx-auto px-6 lg:px-5 flex flex-col lg:flex-row items-center justify-evenly">

        <div class="text-white max-w-lg m-7">
            <div class="flex items-center gap-1 mb-2">
                <div class="w-20 h-20 flex items-center justify-center">
                    <img src="assets/PLV-Logo-2.svg" alt="" class="h-[90px] w-auto">
                </div>
                <div class="flex flex-col items-center h-auto w-auto">
                    <img src="assets/PLV-AlumNet LETTERMARK LOGO_FINAL 1.png" alt="" class="h-auto w-auto ">
                    <p class="font-[Montserrat] font-regular text-xs tracking-widest uppercase text-center">Pamantasan
                        ng Lungsod ng Valenzuela</p>
                    <p class="text-xs text-center opacity-80 uppercase">Alumni Connect</p>
                </div>
                <div class="flex items-center justify-center">
                    <img src="assets/PLV-AlumNet LOGOMARK_WHITE.svg" alt="" class="h-[90px] w-auto">
                </div>
            </div>
            <h2 class=" font-light text-center">Honoring the Past. Shaping the Future.</h2>
        </div>

        <div class="bg-white rounded-[2rem] shadow-2xl p-10 w-full max-w-md text-center">
            <h2 class="inline-block text-4xl font-bold mb-6 bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">Forgot <br> Password</h2>

            <p class="bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent text-sm mb-8 px-4 leading-relaxed">
                Enter the email address associated with your <span class="text-red-700 font-semibold">PLV-AlumNet</span> account.
                <br><br>
                We'll send you a link to reset your password.
            </p>

            <form id="resetForm" action="{{ route('passReset.forgetPasswordPost') }}" method="POST" class="space-y-4">
                @csrf
                <input type="email"
                    name="email"
                    id="emailInput"
                    required
                    placeholder="juan.delacruz@plv.edu.ph"
                    class="w-full px-4 py-3 border border-[#0E0F3B] rounded-md text-black focus:outline-none focus:border-[#C73D1A] transition-all text-center">

                <button type="submit"
                    class="w-full bg-[#0E0F3B] text-white font-bold py-3 rounded-md hover:bg-blue-900 transition-colors tracking-widest text-sm uppercase">
                    SEND A RESET LINK
                </button>
            </form>

            <div class="mt-6">
                <a href="{{ route('auth.login') }}" class="text-orange-600 font-semibold underline decoration-2 underline-offset-4 hover:text-orange-700 text-sm">
                    Back to Login
                </a>
            </div>
        </div>
    </div>

    <div id="successModal" class="hidden-modal fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">
        <div class="bg-white rounded-[2rem] shadow-2xl p-10 w-full max-w-md text-center animate-in fade-in zoom-in duration-300">

            <div class="flex justify-center mb-6">
                <div class="relative text-[#bc4b26]">
                    <i class="fa-solid fa-envelope text-8xl"></i>
                    <div class="absolute -bottom-2 -right-2 bg-[#bc4b26] text-white rounded-full h-9 w-9 flex items-center justify-center border-4 border-white">
                        <i class="fa-solid fa-check text-lg"></i>
                    </div>
                </div>
            </div>

            <h2 class="text-4xl font-bold mb-4 bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent">Check your inbox</h2>

            <div id="modalBodyText" class="bg-gradient-to-r from-[#0E0F3B] via-[#C73D1A] to-[#ED7A07] bg-clip-text text-transparent text-sm space-y-1 mb-8 leading-relaxed">
                
                <p id="messageLine1">A password reset link has been sent to</p>
                <p class="font-bold text-base" id="displayEmail">[User Email]</p>
                <p id="messageLine2" class="mt-4">Please follow the instructions in the email to regain access to your account.</p>
            </div>

            <a href="{{ route('auth.login') }}" class="block w-full bg-[#0E0F3B] text-white font-bold py-4 rounded-md tracking-widest text-sm hover:bg-blue-900 transition-all text-center uppercase">
                BACK TO LOGIN
            </a>

            <div class="mt-6 text-xs text-[#C73D1A]">
                <div id="resendContainer">
                    <p>Didn't receive an email?
                        <button onclick="startResendTimer()" class="underline font-bold ml-1 hover:text-orange-700">Resend link</button>
                    </p>
                </div>
                <div id="timerContainer" class="hidden">
                    <p>Wait [<span id="countdown" class="font-bold">60</span>]s to resend again</p>
                </div>
            </div>
        </div>
    </div>

</body>

<script>
    const resetForm = document.getElementById('resetForm');
    const successModal = document.getElementById('successModal');
    const emailInput = document.getElementById('emailInput');
    const displayEmail = document.getElementById('displayEmail');

    // Handle Form Submission
    resetForm.addEventListener('submit', (e) => {
       // Prevent page reload

        // Show the user's email in the modal
        displayEmail.textContent = emailInput.value;

        // Show the modal
        successModal.classList.remove('hidden-modal');
    });

    // Function to close the modal
    function closeModal() {
        // This will redirect the browser to your login page
        window.location.href = 'login.php';
    }

    //resend email
    let countdownInterval;

    function startResendTimer() {
        const line1 = document.getElementById('messageLine1');
        const line2 = document.getElementById('messageLine2');
        const resendContainer = document.getElementById('resendContainer');
        const timerContainer = document.getElementById('timerContainer');
        const countdownDisplay = document.getElementById('countdown');

        // 1. Swap the text content to match Image 2
        line1.textContent = "We’ve sent a new password reset link to";
        line2.textContent = "Please check your spam or junk folder if you don't see it in a few minutes.";

        // 2. Hide Resend Link and Show Timer
        resendContainer.style.display = 'none';
        timerContainer.classList.remove('hidden');

        // 3. Start Timer Logic
        let timeLeft = 60;
        countdownDisplay.textContent = timeLeft;

        clearInterval(countdownInterval); // Prevent duplicate timers
        countdownInterval = setInterval(() => {
            timeLeft--;
            countdownDisplay.textContent = timeLeft;

            if (timeLeft <= 0) {
                clearInterval(countdownInterval);
                // Reset UI back to resend option
                resendContainer.style.display = 'block';
                timerContainer.classList.add('hidden');
            }
        }, 1000);
    }
</script>

</html>