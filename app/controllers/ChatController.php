<?php
namespace App\Controllers;

class ChatController {
    private $apiKey = "TON_API_KEY_GEMINI";

    public function process() {
        $input = json_decode(file_get_contents('php://input'), true);
        $userMessage = $input['message'] ?? '';

        // Le rôle qu'on donne à l'IA
        $systemPrompt = "Tu es l'assistant de Workspace Connect. 
        Ton but est d'extraire : ressource (salle, bureau, parking), date, heure_debut, heure_fin.
        Si c'est une salle, demande obligatoirement le nombre de participants et leurs noms.
        Réponds TOUJOURS au format JSON strict : 
        {
          'complet': true/false, 
          'donnees': {'type': '...', 'date': '...', 'debut': '...', 'fin': '...', 'participants': '...'},
          'reponse': 'Ton message à l'utilisateur'
        }";

        $response = $this->callGemini($systemPrompt . "\nUtilisateur: " . $userMessage);
        
        header('Content-Type: application/json');
        echo $response;
    }

    private function callGemini($prompt) {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $this->apiKey;

        $data = [
            "contents" => [["parts" => [["text" => $prompt]]]]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $result = curl_exec($ch);
        $json = json_decode($result, true);
        
        // On extrait juste le texte de la réponse de Gemini
        return $json['candidates'][0]['content']['parts'][0]['text'] ?? '{"reponse": "Erreur IA"}';
    }
}