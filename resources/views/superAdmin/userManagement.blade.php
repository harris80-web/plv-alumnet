<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
</head>

<body>
    <div class="flex flex-col w-full items-center">
        <h1>User Management</h1>
        <br><br><br>
        <div>
            <h2 class="flex justify-center font-bold">Employer management</h2>
            @foreach ($employers as $employer)
            <?php if ($employer->user->user_active == 0) {; ?>

                <div class="flex gap-4 border-2 border-gray-300 rounded-lg p-4 mb-4">
                    <div>{{ $loop->iteration }}</div>
                    <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                        <h2>Company name:</h2>
                        <p>{{ $employer->employer_company_name }}</p>
                    </div>
                    <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                        <h2>Website URL:</h2>
                        <p>{{ $employer->employer_website_url ?? 'N/A' }}</p>
                    </div>
                    <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                        <h2>First name:</h2>
                        <p>{{ $employer->user->user_first_name }}</p>
                    </div>
                    <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                        <h2>Last name:</h2>
                        <p>{{ $employer->user->user_last_name }}</p>
                    </div>
                    <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                        <h2>Email:</h2>
                        <p>{{ $employer->user->user_email }}</p>
                    </div>
                    <form action="{{ route('users.approveEmployer', $employer->user_id) }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            Approve
                        </button>
                    </form>

                </div>
            <?php }; ?>
            @endforeach

        </div>
        <br><br><br>
        <div>
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <h2 class="font-bold">Alumni management</h2>
            <div>
                <form action="{{ route('users.addAlumnus') }}" method="POST">
                    @csrf
                    <div>
                        <label for="user_first_name">First Name:</label>
                        <input type="text" id="user_first_name" name="user_first_name" class="border-2 border-gray-300 rounded-lg p-1">
                    </div>
                    <div>
                        <label for="user_last_name">Last Name:</label>
                        <input type="text" id="user_last_name" name="user_last_name" class="border-2 border-gray-300 rounded-lg p-1">
                    </div>
                    <div>
                        <label for="user_middle_name">Middle Name:</label>
                        <input type="text" id="user_middle_name" name="user_middle_name" class="border-2 border-gray-300 rounded-lg p-1">
                    </div>
                    <div>
                        <label for="user_suffix">Suffix:</label>
                        <input type="text" id="user_suffix" name="user_suffix" class="border-2 border-gray-300 rounded-lg p-1">
                    </div>
                    <div>
                        <label for="program_id">Program:</label>
                        <select name="program_id" id="program_id" class="border-2 border-gray-300 rounded-lg p-1">
                            @foreach ($programs as $program)
                            <option value="{{ $program->program_id }}">{{ $program->program_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="alumnus_batch">Batch:</label>
                        <input type="number" id="alumnus_batch" name="alumnus_batch" class="border-2 border-gray-300 rounded-lg p-1">
                    </div>
                    <div>
                        <label for="alumnus_section">Section:</label>
                        <select name="section_id" id="alumnus_section" class="border-2 border-gray-300 rounded-lg p-1">
                            @foreach ($sections as $section)
                            <option value="{{ $section->section_id }}">{{ $section->section_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="user_email">Email:</label>
                        <input type="email" id="user_email" name="user_email" class="border-2 border-gray-300 rounded-lg p-1">
                    </div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Add Alumni
                    </button>
                </form>
            </div>
            <div class="flex flex-col">
                @foreach ($alumni as $alumnus)
            <div class="flex gap-4 border-2 border-gray-300 rounded-lg p-4 mb-4">
                <div>{{ $loop->iteration }}</div>
                <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                    <h2>First name:</h2>
                    <p>{{ $alumnus->user->user_first_name }}</p>
                </div>
                <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                    <h2>Last name:</h2>
                    <p>{{ $alumnus->user->user_last_name }}</p>
                </div>
                <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                    <h2>Middle name:</h2>
                    <p>{{ $alumnus->user->user_middle_name }}</p>
                </div>
                <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                    <h2>Suffix:</h2>
                    <p>{{ $alumnus->user->user_suffix }}</p>
                </div>
                <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                    <h2>Program:</h2>
                    <p>{{ $alumnus->program->program_name }}</p>
                </div>
                <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                    <h2>Batch:</h2>
                    <p>{{ $alumnus->alumnus_batch }}</p>
                </div>
                <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                    <h2>Section:</h2>
                    <p>{{ $alumnus->section->section_name }}</p>
                </div>
                <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                    <h2>Email:</h2>
                    <p>{{ $alumnus->user->user_email }}</p>
                </div>
                
            </div>
            @endforeach
            </div>
            
        </div>
        <br><br><br>
        <div>
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <h2 class="font-bold">Admin management</h2>
            <div>
                <form action="{{ route('users.addAdmin') }}" method="POST">
                    @csrf
                    <div>
                        <label for="user_first_name">First Name:</label>
                        <input type="text" id="user_first_name" name="user_first_name" class="border-2 border-gray-300 rounded-lg p-1">
                    </div>
                    <div>
                        <label for="user_last_name">Last Name:</label>
                        <input type="text" id="user_last_name" name="user_last_name" class="border-2 border-gray-300 rounded-lg p-1">
                    </div>
                    <div>
                        <label for="user_middle_name">Middle Name:</label>
                        <input type="text" id="user_middle_name" name="user_middle_name" class="border-2 border-gray-300 rounded-lg p-1">
                    </div>
                    <div>
                        <label for="user_suffix">Suffix:</label>
                        <input type="text" id="user_suffix" name="user_suffix" class="border-2 border-gray-300 rounded-lg p-1">
                    </div>
                    <div>
                        <label for="office_address">Address:</label>
                        <input type="text" name="office_address" class="border-2 border-gray-300 rounded-lg p-1">
                    </div>
                    <div>
                        <label for="user_email">Email:</label>
                        <input type="email" id="user_email" name="user_email" class="border-2 border-gray-300 rounded-lg p-1">
                    </div>
                    <div>
                        <label for="user_password">Password:</label>
                        <input type="password" id="user_password" name="user_password" class="border-2 border-gray-300 rounded-lg p-1">
                    </div>
                    <div>
                        <label for="user_password_confirmation">Confirm password:</label>
                        <input type="password" id="user_password_confirmation" name="user_password_confirmation" class="border-2 border-gray-300 rounded-lg p-1">
                    </div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Add Admin
                    </button>
                </form>
            </div>
            <div class="flex flex-col">
                @foreach ($admins as $admin)
            <div class="flex gap-4 border-2 border-gray-300 rounded-lg p-4 mb-4">
                <div>{{ $loop->iteration }}</div>
                <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                    <h2>First name:</h2>
                    <p>{{ $admin->user->user_first_name }}</p>
                </div>
                <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                    <h2>Last name:</h2>
                    <p>{{ $admin->user->user_last_name }}</p>
                </div>
                <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                    <h2>Middle name:</h2>
                    <p>{{ $admin->user->user_middle_name }}</p>
                </div>
                <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                    <h2>Suffix:</h2>
                    <p>{{ $admin->user->user_suffix }}</p>
                </div>
                <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                    <h2>Address:</h2>
                    <p>{{ $admin->office_address }}</p>
                </div>
                <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                    <h2>Account status:</h2>
                    <p>{{ $admin->user->user_active }}</p>
                </div>
                <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                    <h2>Date created:</h2>
                    <p>{{ $admin->user->created_at->format('Y-m-d') }}</p>
                </div>
                <div class="flex flex-col items-center border-2 border-gray-300 rounded-lg p-1">
                    <h2>Email:</h2>
                    <p>{{ $admin->user->user_email }}</p>
                </div>
                
            </div>
            @endforeach
            </div>
            
        </div>

</body>
</html>