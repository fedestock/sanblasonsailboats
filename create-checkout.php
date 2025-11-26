<?php
header('Content-Type: application/json');

$config = include __DIR__ . '/config.php';
$stripeSecret = $config['stripe_secret'];

$input = json_decode(file_get_contents("php://input"), true);

$fecha = $input["fecha"] ?? "sin-fecha";
$experiencia = $input["experiencia"] ?? "experiencia";
$personas = intval($input["personas"] ?? 1);

$precio_por_persona = 1000; // ejemplo en USD
$total = $precio_por_persona * $personas;
$total_cents = $total * 100; // Stripe trabaja en centavos

// Datos para Stripe Checkout
$data = [
    "payment_method_types" => ["card"],
    "mode" => "payment",
    "line_items" => [[
        "price_data" => [
            "currency" => "usd",
            "product_data" => [
                "name" => $experiencia . " - " . $fecha,
            ],
            "unit_amount" => $total_cents
        ],
        "quantity" => 1
    ]],
    "success_url" => "http://localhost:8000/success.html",
    "cancel_url"  => "http://localhost:8000/reservar.html?exp=" . urlencode($experiencia),
    "metadata" => [
        "fecha" => $fecha,
        "experiencia" => $experiencia,
        "personas" => $personas
    ]
];

// Ejecutar llamada a Stripe API vía cURL
$ch = curl_init("https://api.stripe.com/v1/checkout/sessions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $stripeSecret",
    "Content-Type: application/x-www-form-urlencoded"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpcode >= 200 && $httpcode < 300) {
    // Stripe devuelve JSON; lo decodificamos para obtener la URL
    $session = json_decode($response, true);
    echo json_encode([
        "url" => $session["url"] ?? null
    ]);
} else {
    echo json_encode([
        "error" => "Error al conectar con Stripe",
        "stripe_response" => $response
    ]);
}
