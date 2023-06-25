<?php
/*include ROOTPATH . '/includes/lib/StableDiffusion.php';

$stableDiffusion = new StableDiffusion(get_image_api_key('stable-diffusion'));


$response = $stableDiffusion->listEngines();
print_r($response);*/
$text = 'tass';
$bad_words = 'bác hồ, hồ chí minh, đảng cộng sản, ass';
$bad_words = explode(',', $bad_words);
$bad_words = array_map('trim', $bad_words);

foreach ($bad_words as $word) {
    // Search for the word in string
    $regex_start = '/(^|\b|\s)';
    $regex_end = '(\b|\s|$)/i';
    if (preg_match($regex_start . '(' . preg_quote(mb_strtolower($word), '/') . ')' . $regex_end, mb_strtolower($text))) {
        echo $word;
    }
}