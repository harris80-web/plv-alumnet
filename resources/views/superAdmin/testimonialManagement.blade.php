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
    <h1>MANAGE TESTIMONIALS</h1>
    <div>
        @foreach($testimonials as $testimonial)
        @if (!$testimonial->testimonial_post)
        <div>
            <p>{{ $testimonial->testimonial_body }}</p>
            <p>Submitted by: {{ $testimonial->alumnus->user->user_first_name }}</p>
        </div>
        <form action="{{ route('testimonials.post', $testimonial->testimonial_id) }}" method="POST">
            @csrf
            @method('PUT')
            <button type="submit">Post</button>
        </form>
        @endif
        @endforeach
    </div>
</body>

</html>