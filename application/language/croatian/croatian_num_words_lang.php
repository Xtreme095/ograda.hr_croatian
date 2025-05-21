<?php

$lang['num_word_0']        = 'Nula';
$lang['num_word_1']        = 'Jedan';
$lang['num_word_2']        = 'Dva';
$lang['num_word_3']        = 'Tri';
$lang['num_word_4']        = 'Četiri';
$lang['num_word_5']        = 'Pet';
$lang['num_word_6']        = 'Šest';
$lang['num_word_7']        = 'Sedam';
$lang['num_word_8']        = 'Osam';
$lang['num_word_9']        = 'Devet';
$lang['num_word_10']       = 'Deset';
$lang['num_word_11']       = 'Jedanaest';
$lang['num_word_12']       = 'Dvanaest';
$lang['num_word_13']       = 'Trinaest';
$lang['num_word_14']       = 'Četrnaest';
$lang['num_word_15']       = 'Petnaest';
$lang['num_word_16']       = 'Šesnaest';
$lang['num_word_17']       = 'Sedamnaest';
$lang['num_word_18']       = 'Osamnaest';
$lang['num_word_19']       = 'Devetnaest';
$lang['num_word_20']       = 'Dvadeset';
$lang['num_word_21']       = 'Dvadeset jedan';
$lang['num_word_22']       = 'Dvadeset dva';
$lang['num_word_23']       = 'Dvadeset tri';
$lang['num_word_24']       = 'Dvadeset četiri';
$lang['num_word_25']       = 'Dvadeset pet';
$lang['num_word_26']       = 'Dvadeset šest';
$lang['num_word_27']       = 'Dvadeset sedam';
$lang['num_word_28']       = 'Dvadeset osam';
$lang['num_word_29']       = 'Dvadeset devet';
$lang['num_word_30']       = 'Trideset';
$lang['num_word_31']       = 'Trideset jedan';
// Nastavi prema uzorku za sve brojeve do 99

$lang['num_word_100']      = 'Sto';
$lang['num_word_200']      = 'Dvije stotine';
$lang['num_word_300']      = 'Tri stotine';
$lang['num_word_400']      = 'Četiri stotine';
$lang['num_word_500']      = 'Pet stotina';
$lang['num_word_600']      = 'Šest stotina';
$lang['num_word_700']      = 'Sedam stotina';
$lang['num_word_800']      = 'Osam stotina';
$lang['num_word_900']      = 'Devet stotina';
$lang['num_word_thousand'] = 'Tisuća';
$lang['num_word_million']  = 'Milijun';
$lang['num_word_billion']  = 'Milijarda';
$lang['num_word_trillion'] = 'Bilijun';
$lang['num_word_zillion']  = 'Zilijun';
$lang['num_word_cents']    = 'Centa';
$lang['number_word_and']   = 'I';
$lang['number_word_only']  = 'Samo';

// Za indijske korisnike, korišteno s INR valutom
$lang['num_word_hundred'] = 'Sto';
$lang['num_word_lakh']    = 'Lakh';
$lang['num_word_lakhs']   = 'Lakh';
$lang['num_word_crore']   = 'Crore';
$lang['num_word_paisa']   = 'Paisa';

// AED valuta, Fils umjesto Centi
$lang['num_word_cents_AED'] = 'Fils';

// Prikaži na fakturama i procjenama
$lang['num_word'] = 'U riječima';

$currencies = [
    'USD' => 'Dolari',
    'EUR' => 'Euri',
    'INR' => 'Rupije',
    'AED' => 'Dirhami',
];

$currencies = hooks()->apply_filters('before_number_format_render_language_currencies', $currencies);

foreach ($currencies as $key => $val) {
    $lang['num_word_' . strtoupper($key)] = $val;
}
?>
```