<?php
namespace App\Controllers;

class ChatController {
    private $apiKey;

    public function __construct() {
        // On charge le fichier de config
        $config = require __DIR__ . '/../../config/api_config.php';
        $this->apiKey = $config['gemini_key'];
    }

public function process() {
    $input = json_decode(file_get_contents('php://input'), true);
    $userMessage = $input['message'] ?? '';

    $systemPrompt = "Tu es l'assistant de Workspace Connect. 
Réponds UNIQUEMENT avec ce format JSON : 
{
  \"complet\": true ou false,
  \"donnees\": {\"type\": \"...\", \"date\": \"...\", \"debut\": \"...\", \"fin\": \"...\"},
  \"reponse\": \"Ton message ici\"
}";

    $fullPrompt = $systemPrompt . "\nMessage de l'utilisateur : " . $userMessage;

    $response = $this->callGemini($fullPrompt);
    
    header('Content-Type: application/json');
    echo $response;
}

private function callGemini($prompt) {
    $config = require __DIR__ . '/../../config/api_config.php';
    $apiKey = trim($config['gemini_key']);
    
    $url = "https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash:generateContent?key=" . $apiKey;

    $data = [
        "contents" => [
            ["parts" => [["text" => $prompt]]]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        $error = json_decode($result, true);
        return json_encode([
            "reponse" => "Erreur " . $httpCode,
            "detail_google" => $error['error']['message'] ?? 'Erreur inconnue'
        ]);
    }

    $json = json_decode($result, true);
    
    // Extraction de la réponse texte
    if (isset($json['candidates'][0]['content']['parts'][0]['text'])) {
        $aiResponse = $json['candidates'][0]['content']['parts'][0]['text'];
        // Nettoyage du Markdown JSON si l'IA en ajoute
        return preg_replace('/^```json|```$/m', '', $aiResponse);
    }

    return json_encode(["reponse" => "L'IA n'a pas renvoyé de texte validable."]);
}
}