<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Confirmação de E-mail</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        .header {
            background-color: #4f46e5;
            padding: 24px;
            text-align: center;
        }

        .header img {
            max-width: 120px;
            height: auto;
        }

        .content {
            padding: 30px;
            color: #333333;
        }

        .content h2 {
            margin-top: 0;
            font-size: 24px;
            color: #111827;
        }

        .content p {
            font-size: 16px;
            line-height: 1.5;
        }

        .button-wrapper {
            text-align: center;
            margin: 30px 0;
        }

        .button {
            background-color: #4f46e5;
            color: #ffffff;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            display: inline-block;
        }

        .footer {
            background-color: #f9fafb;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: #6b7280;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <img src="{{ asset('images/logo.png') }}" alt="Logo" style="height: 60px; margin-bottom: 20px;">
    </div>
    <div class="content">
        <h2>Olá, {{ $user->name ?? 'usuário' }} 👋</h2>
        <p>
            Obrigado por se registrar em nossa plataforma. Para ativar sua conta e começar a usar todos os recursos,
            por favor clique no botão abaixo para confirmar seu endereço de e-mail.
        </p>

        <div class="button-wrapper">
            <a href="{{ $url }}" class="button">Confirmar E-mail</a>
        </div>

        <p>
            Caso você não tenha criado esta conta, pode ignorar este e-mail com segurança.
        </p>

        <p>Atenciosamente,<br/>Equipe {{ config('app.name') }}</p>
    </div>
    <div class="footer">
        © {{ date('Y') }} {{ config('app.name') }}. Todos os direitos reservados.
    </div>
</div>
</body>
</html>
