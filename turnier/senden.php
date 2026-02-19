<?php
// =============================================================
// CONFIGURATION / EINSTELLUNGEN
// =============================================================

// 1. Enter YOUR Email Address here (Organizer)
$organizer_email = "vorstand@sc-boeblingen.de"; 

// 2. Subject Line for YOU
$subject_organizer = "Neue Turnieranmeldung (Webseite)";

// 3. Subject Line for the PLAYER
$subject_player = "Anmeldebestätigung / Confirmation - Böblinger Open";

// =============================================================
// DATA PROCESSING
// =============================================================

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Collect Data (Safe reading)
    $anrede = htmlspecialchars($_POST['anrede']);
    $titel = htmlspecialchars($_POST['titel']);
    $vorname = htmlspecialchars($_POST['vorname']);
    $nachname = htmlspecialchars($_POST['nachname']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $telefon = htmlspecialchars($_POST['telefon']);
    $geburtsdatum = htmlspecialchars($_POST['geburtsdatum']);
    $verein = htmlspecialchars($_POST['verein']);
    $fide_id = htmlspecialchars($_POST['fide_id']);
    $fide_titel = htmlspecialchars($_POST['fide_titel']);
    $dwz = htmlspecialchars($_POST['dwz']);
    $elo = htmlspecialchars($_POST['elo']);
    $gruppe = htmlspecialchars($_POST['gruppe']);
    $kommentar = htmlspecialchars($_POST['kommentar']);
    
    // Address (Optional fields)
    $strasse = isset($_POST['strasse']) ? htmlspecialchars($_POST['strasse']) : '';
    $plz = isset($_POST['plz']) ? htmlspecialchars($_POST['plz']) : '';
    $ort = isset($_POST['ort']) ? htmlspecialchars($_POST['ort']) : '';
    $land = isset($_POST['land']) ? htmlspecialchars($_POST['land']) : '';

    // =========================================================
    // 1. SAVE TO CSV (Excel Backup)
    // =========================================================
    
    $csvFile = 'anmeldungen.csv';
    $isNew = !file_exists($csvFile);
    $fp = fopen($csvFile, 'a'); // 'a' = append

    // Add Header if file is new
    if ($isNew) {
        // UTF-8 BOM so Excel reads special characters (ä,ö,ü) correctly
        fputs($fp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
        fputcsv($fp, array('Datum', 'Name', 'Vorname', 'Verein', 'Email', 'Gruppe', 'DWZ', 'ELO', 'Geburtsdatum'), ';');
    }

    // Add Data Row (using semicolon ; for German Excel compatibility)
    fputcsv($fp, array(date("d.m.Y H:i"), $nachname, $vorname, $verein, $email, $gruppe, $dwz, $elo, $geburtsdatum), ';');
    fclose($fp);

    // =========================================================
    // 2. EMAIL TO ORGANIZER
    // =========================================================
    
    $msg_org = "Neue Anmeldung über die Webseite:\n\n";
    $msg_org .= "Name: $anrede $titel $vorname $nachname\n";
    $msg_org .= "Verein: $verein\n";
    $msg_org .= "Gruppe: $gruppe\n";
    $msg_org .= "Rating: DWZ $dwz / ELO $elo\n";
    $msg_org .= "Email: $email\n";
    $msg_org .= "Geburtstag: $geburtsdatum\n";
    $msg_org .= "Kommentar: $kommentar\n";
    
    // Header for Organizer Email
    $headers_org = "From: noreply@sc-boeblingen.de\r\n";
    $headers_org .= "Reply-To: $email\r\n";
    $headers_org .= "Content-Type: text/plain; charset=UTF-8\r\n";

    // Send it
    mail($organizer_email, $subject_organizer, $msg_org, $headers_org);

    // =========================================================
    // 3. EMAIL TO PLAYER (Autoresponder)
    // =========================================================

    $msg_player = "Hallo $vorname $nachname,\n\n";
    $msg_player .= "Vielen Dank für Ihre Anmeldung zum Grandmaster Open! / Thank you for registering.\n\n";
    $msg_player .= "Ihre Daten / Your Data:\n";
    $msg_player .= "---------------------------------\n";
    $msg_player .= "Gruppe: $gruppe\n";
    $msg_player .= "Verein: $verein\n";
    $msg_player .= "---------------------------------\n\n";
    $msg_player .= "ZAHLUNGSHINWEIS / PAYMENT:\n";
    $msg_player .= "Bitte überweisen Sie das Startgeld auf folgendes Konto:\n";
    $msg_player .= "IBAN: DE12 3456 7890 1234 5678 90\n";
    $msg_player .= "BIC: GENODED1XYZ\n";
    $msg_player .= "Verwendungszweck: Open 2026 + Ihr Name\n\n";
    $msg_player .= "Mit freundlichen Grüßen,\n";
    $msg_player .= "Ihr Turnier-Team";

    // Header for Player Email
    $headers_player = "From: $organizer_email\r\n";
    $headers_player .= "Content-Type: text/plain; charset=UTF-8\r\n";

    // Send it (Only if email field wasn't empty)
    if (!empty($email)) {
        mail($email, $subject_player, $msg_player, $headers_player);
    }

    // =========================================================
    // 4. REDIRECT TO SUCCESS PAGE
    // =========================================================
    
    header("Location: danke.html");
    exit();

} else {
    // If someone tries to open senden.php directly without submitting form
    header("Location: anmeldung.html");
    exit();
}
?>