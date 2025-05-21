<?php

defined('BASEPATH') or exit('No direct script access allowed');
$dimensions = $pdf->getPageDimensions();

// Company Header Section
$company_info = '
    <div style="text-align: left;">
        <strong>Profi line Zagreb d.o.o.</strong><br />
        Gojlanska ulica 47<br />
        10000 Zagreb, Hrvatska<br />
        OIB: 62145316954 | MBS: 081065807<br />
        Tel: +385 1/400-1500 | IBAN: HR8524840081135398854<br />
        SWIFT: PBZGHR2X<br />
    </div>';
$pdf->writeHTMLCell(0, 0, '', '', $company_info, 0, 1, false, true, 'L', true);

$pdf->ln(4); // Line break

// Client Information Section
/*$client_info = '
    <div style="text-align: right;">
        <strong>' . $client->company . '</strong><br />
        ' . $client->address . '<br />
        ' . $client->zip . ', ' . $client->city . '<br />
        OIB: ' . $client->oib . '<br />
        Contact: ' . $client->phone . '<br />
    </div>';
$pdf->writeHTMLCell(0, 0, '', '', $client_info, 0, 1, false, true, 'R', true); */

$pdf->ln(6); // Line break
$nalog = $proposal->id . '/' . date('Y', strtotime($proposal->date));
// Proposal Title Section
//$proposal_title = '<h2 style="text-align: center;">Nalog prodaje br. ' . $proposal->number . '</h2>';
$proposal_title = '<h2 style="text-align: center;">Nalog prodaje br. '.$number.'</h2>';
$pdf->writeHTMLCell(0, 0, '', '', $proposal_title, 0, 1, false, true, 'C', true);

// Document Info Section
//print_r($proposal);exit;


$document_info = '
    <table cellspacing="5" cellpadding="5">
        <tr>
            <td><strong>Datum izdavanja:</strong> ' . _d($proposal->date) . '</td>
            <td><strong>Mjesto izdavanja:</strong> Zagreb</td>
        </tr>
        <tr>';
            /*<td><strong>Dokument kreirao:</strong> ' . $proposal->created_by . '</td>*/
            
            $document_info .= '</tr>
    </table>';
$pdf->writeHTMLCell(0, 0, '', '', $document_info, 0, 1, false, true, 'L', true);

$pdf->ln(4); // Line break

// Product Table Header
$html = '
    <style>
        table  {
            margin: 0px;
        }
    </style>
    <table border="0" cellspacing="0" cellpadding="6">
        <thead>
            <tr>
                <th style="text-align: left;" width="50px">RB</th>
                <th style="text-align: left; " width="350px">Naziv proizvoda/usluge</th>
                <th style="text-align: right;" width="80px">Količina</th>
                <th style="text-align: right;" width="80px">JM</th>
                <th style="text-align: right;" width="100px">Cijena</th>
                <th style="text-align: right;" width="100px">PDV</th>
                <th style="text-align: right;" width="100px">Ukupno</th>
            </tr>
        </thead>
        <tbody>';
//$pdf->writeHTML($table_header, true, false, false, false, '');

// Product Rows
foreach ($proposal->items as $index => $item) {    
    $fields = trim($item['fields'], ';');
    $fields = explode(';',$fields);
    $size = 300;
    if(count($fields) == 2) {
        $brs = '<br><br><br>';
    } else if(count($fields) == 3) {
        $brs = '<br><br>';
        $size = 400;
    } else{
        $brs = '<br><br>';
        if(count($fields) == 4) {
            $size = 450;
        } else if (count($fields) == 5) {
            $size = 450;
        } else if (count($fields) == 6) {
            $size = 500;
        } else if (count($fields) == 7) {
            $size = 550;
        } else if (count($fields) == 8) {
            $size = 600;
        } else if (count($fields) == 9) {
            $size = 650;
        } else {
            $size = 700;
        }
    }
    
    $html .= '
        <tr>
            <td width="50px">' . ($index + 1) . '</td>
            <td width="350px" style="text-align: left;">
            <table border="0" cellspacing="0" cellpadding="0" >
                <tbody>
                    <tr><td collspan="2"><b>'. $item['description'] .'</b><br></td></tr>';
                    if($item['length'] > 0) {
                    $html .= '<tr>
                    
                        <td width="'.$size.'px">
                        <table border="0" style="line-height: -0.8; padding-left:-5px">
                            <tbody>
                            <tr>';
                            if(count($fields) > 0) {
                                foreach ($fields as $field) {
                                    $html .= '<td class="cell">
                                        <img src="https://crm.ograda.hr/uploads/projects/1/fd469980148b2c099c438963183cbe3f.jpg" />
                                    </td>';
                            
                                }
                            }
                            $html .= '</tr>
                            </tbody>
                        </table>
                        </td>
                        <td style="border-bottom: solid 1px black;border-top: solid 1px black;border-right: solid 1px black; text-align:center; " height="30px" width="'.strlen($item['height']).'0px">'. $brs .$item['height'].'</td>
                    </tr>
                    <tr>
                        <td>
                        <table cellspacing="0" cellpadding="0">
                            <tr>';
                            if(count($fields) > 0) {
                                foreach ($fields as $field) {
                                    $html .= '<td style="border-bottom: solid 1px black;border-left: solid 1px black;border-right: solid 1px black; text-align:center";>'.$field.'</td>';
                             
                                }
                                
                            }
                       
                            $html .= '</tr>
                        </table>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>
                        <table cellspacing="0" cellpadding="0">
                        <tr>
                        <td style="border-bottom: solid 1px black;border-left: solid 1px black;border-right: solid 1px black; text-align:center; ">'.$item['length'].'</td></tr></table>
                        </td>
                        <td></td>
                    </tr>';
                    }
                    $html .= '</tbody>
                </table>
            </td>
            <td style="text-align: right;" width="80px">' . $item['qty'] . '</td>
            <td style="text-align: right;" width="80px">' . $item['unit'] . '</td>
            <td style="text-align: right;" width="100px">' . app_format_money($item['rate'], $proposal->currency_name) . '</td>
            <td style="text-align: right;" width="100px">PDV 25%</td>
            <td style="text-align: right;" width="100px">' . app_format_money($item['qty']*$item['rate'], $proposal->currency_name) . '</td>
        </tr>';
        
    //$pdf->writeHTML($row, true, false, false, false, '');
}

// Table Footer (Totals)
$html .= '
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" style="text-align: right;"><strong>Ukupno bez PDV-a:</strong></td>
                <td style="text-align: right;">' . app_format_money($proposal->subtotal, $proposal->currency_name) . '</td>
            </tr>
            <tr>
                <td colspan="6" style="text-align: right;"><strong>PDV:</strong></td>
                <td style="text-align: right;">' . app_format_money($proposal->total_tax, $proposal->currency_name) . '</td>
            </tr>
            <tr>
                <td colspan="6" style="text-align: right;"><strong>Ukupno za plaćanje:</strong></td>
                <td style="text-align: right;">' . app_format_money($proposal->total, $proposal->currency_name) . '</td>
            </tr>
        </tfoot>
    </table>';
$pdf->writeHTML($html, true, false, false, false, '');

// Footer Section
$footer = '
<p style="width:300px; text-align: right;"></p> 
    <p style="font-size: 10px; text-align: center;">
        Profi line Zagreb d.o.o. | Gojlanska ulica 47, 10000 Zagreb, Hrvatska | OIB: 62145316954
        | IBAN: HR8524840081135398854 | SWIFT: PBZGHR2X
    </p>
    
    ';

    

$pdf->writeHTML($footer, true, false, true, false, 'C');

$pdf->write2DBarcode($bar, 'PDF417', 140, 215, 50, 50, $style, 'N');
$pdf->Text(140, 210, 'Kod za plaćanje:');
