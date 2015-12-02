<?php
ini_set('memory_limit', '4096M');
ini_set('max_execution_time', '1200');
set_time_limit(0);

$timestamp = time();

mkdir(getcwd()."/phar{$timestamp}");
copy('httpful.phar', "phar{$timestamp}/httpful.phar");

require_once("./phar{$timestamp}/httpful.phar");

$date = date("mdY");
?>

<form action="" method="post">
    <p>Please select distributor to export</p>
    <select name="distributorId">
        <option value="">-- Select Distributor --</option>
        <option value="1">Tucker Rocky</option>
        <option value="2">Parts Unlimited</option>
        <option value="3">Western Powersports</option>
        <option value="5">Polaris</option>
        <option value="7">Can-Am</option>
        <option value="8">Fox</option>
        <option value="10">Helmet House</option>
        <option value="11">Honda</option>
        <option value="12">Kawasaki</option>
        <option value="14">Sea-Doo</option>
        <option value="15">Ski-Doo</option>
        <option value="16">Suzuki</option>
        <option value="18">Yamaha</option>
        <option value="29">Troy Lee Designs</option>
        <option value="33">Scorpion</option>
        <option value="46">Bell Helmets</option>
    </select>
    <input type="submit" value="Submit" />
</form>

<?php
$distri = $_POST['distributorId'];

if(isset($distri)){
    
    $distributorName = array(
        1 => 'tucker-rocky',
        2 => 'parts-unlimited',
        3 => 'western-powersports',
        5 => 'polaris',
        7 => 'can-am',
        8 => 'fox',
        10 => 'helmet-house',
        11 => 'honda',
        12 => 'kawasaki',
        14 => 'sea-doo',
        15 => 'ski-doo',
        16 => 'suzuki',
        18 => 'yamaha',
        29 => 'troy-lee-designs'
    );
    
    $applicationKey = 'N8SZjBuVQoU6EhkxtCi2';
    $activitiesUrl = 'http://accessorystream.arinet.com/RestAPI/export/distributorskus/'.$distri.'?field=ActivityIDS&field=CategoryID&field=SubcategoryID&field=Category&field=SubCategory&field=AttributeType';
    $response = \Httpful\Request::get($activitiesUrl)->addHeader('applicationKey', $applicationKey)->send();

    $list = $response->body;

    $file = fopen(getcwd()."/csv/{$distributorName[$distri]}-{$distri}-{$timestamp}.csv", "w");    

    foreach ($list as $line) {    
        fputcsv($file, $line);
    }

    fclose($file);
    
    echo "<a href=\"http://www.tmsparts.com/last/ari2/csv/{$distributorName[$distri]}-{$distri}-{$timestamp}.csv\">Download {$distributorName[$distri]}-{$distri}-{$timestamp}.csv</a>";
    
}
?>