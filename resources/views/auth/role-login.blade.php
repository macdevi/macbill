@php
    $title = $title ?? 'Login';
    $roleLabel = $roleLabel ?? 'USER ACCESS';
    $role = $role ?? '';
    $postUrl = $postUrl ?? '/login';
    $backUrl = url('/');
@endphp

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>

    <style>
        *{box-sizing:border-box}
        body{
            margin:0;
            min-height:100vh;
            font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
            color:#101828;
            background:
                radial-gradient(circle at 50% -10%,rgba(37,99,235,.48),transparent 34%),
                linear-gradient(180deg,#0b1220,#111827);
            display:grid;
            place-items:center;
            padding:18px;
        }
        .card{
            width:min(420px,100%);
            overflow:hidden;
            border-radius:30px;
            background:#fff;
            border:1px solid #e4eaf3;
            box-shadow:0 34px 90px rgba(0,0,0,.34);
        }
        .hero{
            padding:26px 24px;
            color:#fff;
            background:linear-gradient(135deg,#0b1b3a,#2563eb 58%,#06b6d4);
        }
        .hero small{
            display:block;
            color:#dbeafe;
            font-size:12px;
            font-weight:900;
            letter-spacing:.14em;
            text-transform:uppercase;
        }
        .hero h1{
            margin:10px 0 0;
            font-size:34px;
            line-height:.96;
            letter-spacing:-.065em;
        }
        form{padding:22px}
        label{
            display:block;
            margin:0 0 7px;
            color:#475467;
            font-size:12px;
            font-weight:900;
        }
        .field{margin-bottom:14px}
        .input{
            width:100%;
            height:48px;
            border-radius:16px;
            border:1px solid #dbe5f2;
            background:#f8fbff;
            padding:0 14px;
            font-size:14px;
            font-weight:800;
            outline:none;
        }
        .error{
            margin:0 0 14px;
            padding:11px 12px;
            border-radius:16px;
            color:#b42318;
            background:#fef3f2;
            border:1px solid #fecaca;
            font-size:12px;
            font-weight:850;
            line-height:1.4;
        }
        .btn{
            width:100%;
            height:50px;
            border:0;
            border-radius:17px;
            color:#fff;
            background:linear-gradient(135deg,#2563eb,#06b6d4);
            font-size:14px;
            font-weight:950;
            cursor:pointer;
            box-shadow:0 16px 34px rgba(37,99,235,.22);
        }
        .back{
            width:100%;
            height:46px;
            margin-top:10px;
            display:flex;
            align-items:center;
            justify-content:center;
            border-radius:16px;
            color:#175cd3;
            background:#eff6ff;
            border:1px solid #bfdbfe;
            font-size:13px;
            font-weight:950;
            text-decoration:none;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="hero">
            <small>{{ $roleLabel }}</small>
            <h1>{{ $title }}</h1>
        </div>

        <form method="POST" action="{{ url($postUrl) }}" id="roleLoginForm">
            @csrf

            @if($errors->any())
                <div class="error">{{ $errors->first() }}</div>
            @endif

            <input type="hidden" name="role" value="{{ $role }}">
<div class="field">
                <label>Username / Email</label>
                <input class="input" type="text" name="login" id="loginIdentity" autocomplete="username" required autofocus>
            </div>

            <div class="field">
                <label>Password</label>
                <input class="input" type="password" name="password" autocomplete="current-password" required>
            </div>

            <button class="btn" type="submit">Masuk</button>
            <a class="back" href="{{ $backUrl }}">Kembali ke Landing Page</a>
        </form>
    </div>

    
</body>
</html>
