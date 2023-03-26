<!DOCTYPE HTML>
<html>
<head>
    <title>@yield('title')</title>
</head>
<body>
    @if (! $user)
        <p>Not Authorized</p>
        <form action="/login" method="GET">
            @csrf
            <button>Login</button>
        </form>
        <form action="/register" method="GET">
            @csrf
            <button>Register</button>
        </form>
    @else
        <div class="container">
            @yield('content')
        </div>
        <div>
            <form action="/logout" method="POST">
                @csrf
                <button>Logout</button>
            </form>
        </div>
    @endif
</body>
</html>
