<!DOCTYPE html>
<html>
<head>
    <title>Test Session & CSRF</title>
</head>
<body>
    <h1>Test Session & CSRF Token</h1>
    
    <h2>Session Info:</h2>
    <pre>
Session ID: {{ session()->getId() }}
Session Driver: {{ config('session.driver') }}
CSRF Token: {{ csrf_token() }}
    </pre>

    <h2>Test Form:</h2>
    <form method="POST" action="/test-csrf">
        @csrf
        <button type="submit">Submit Test</button>
    </form>

    @if(session('test_result'))
        <div style="background: green; color: white; padding: 10px; margin-top: 10px;">
            ✅ CSRF Token Valid! Session Works!
        </div>
    @endif
</body>
</html>
