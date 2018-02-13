<!DOCTYPE html>
<html>
<head>
    <title>Bienvenido</title>
</head>

<body>
<h2>Bienvenido a PlazaShop</h2>
<br/>
    El correo registrado es {{ $user['email'] }}, y tu codigo de registro es el siguiente {{ $user['confirmation_code'] }}
</body>

</html>