<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); 

session_start();


$prizes = [
    [
        'name' => 'Better Luck Next Time',
        'probability' => 0.50,
        'icon' => '😢',
        'segments' => [0, 2, 4]
    ],
    [
        'name' => '50% OFF',
        'probability' => 0.30,
        'icon' => '🎁',
        'segments' => [1, 5]
    ],
    [
        'name' => '100% OFF',
        'probability' => 0.20,
        'icon' => '🎉',
        'segments' => [3]
    ]
];

function getRandomPrize($prizes) {
    $random = mt_rand(1, 100) / 100; 
    $cumulativeProbability = 0;
    
    foreach ($prizes as $prize) {
        $cumulativeProbability += $prize['probability'];
        if ($random <= $cumulativeProbability) {
            $segmentIndex = $prize['segments'][array_rand($prize['segments'])];
            return [
                'prize' => $prize,
                'segmentIndex' => $segmentIndex
            ];
        }
    }
    
    return [
        'prize' => $prizes[0],
        'segmentIndex' => 0
    ];
}

$rateLimitEnabled = true;
$maxSpinsPerMinute = 5;

if ($rateLimitEnabled) {
    if (!isset($_SESSION['spin_times'])) {
        $_SESSION['spin_times'] = [];
    }
    
    $currentTime = time();
    $_SESSION['spin_times'] = array_filter($_SESSION['spin_times'], function($time) use ($currentTime) {
        return ($currentTime - $time) < 60;
    });
    
    if (count($_SESSION['spin_times']) >= $maxSpinsPerMinute) {
        echo json_encode([
            'success' => false,
            'error' => 'Too many spins. Please wait a moment and try again.'
        ]);
        exit;
    }
    $_SESSION['spin_times'][] = $currentTime;
}


$result = getRandomPrize($prizes);
echo json_encode([
    'success' => true,
    'prize' => [
        'name' => $result['prize']['name'],
        'icon' => $result['prize']['icon']
    ],
    'segmentIndex' => $result['segmentIndex']
]);
function logSpin($result) {
    $logFile = 'spin_log.txt';
    $logEntry = date('Y-m-d H:i:s') . " - Prize: " . $result['prize']['name'] . " - Segment: " . $result['segmentIndex'] . "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}
?>