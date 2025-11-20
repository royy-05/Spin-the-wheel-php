<?php
session_start();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['spin'])) {
    $result = getWeightedResult();
    $_SESSION['result'] = $result;
    $_SESSION['show_popup'] = true;
    
    // Redirect to prevent form resubmission
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Handle popup close
if (isset($_GET['close'])) {
    unset($_SESSION['show_popup']);
    unset($_SESSION['result']);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Get weighted random result
function getWeightedResult() {
    $random = rand(0, 100);
    
    $prizes = [
        'lose' => [
            'segments' => [0, 2, 4],
            'probability' => 50,
            'title' => 'Better Luck Next Time!',
            'message' => "Don't give up! Spin again for another chance to win amazing discounts!",
            'icon' => '😢',
            'type' => 'loser',
            'code' => null
        ],
        'win50' => [
            'segments' => [1, 5],
            'probability' => 30,
            'title' => '🎉 You Won 50% OFF! 🎉',
            'message' => 'Congratulations! Use the code below to get 50% off your next purchase!',
            'icon' => '🥳',
            'type' => 'winner',
            'code' => 'SAVE50'
        ],
        'jackpot' => [
            'segments' => [3],
            'probability' => 20,
            'title' => '🏆 JACKPOT! 100% OFF! 🏆',
            'message' => 'AMAZING! You hit the jackpot! Your next purchase is completely FREE!',
            'icon' => '🎊',
            'type' => 'jackpot',
            'code' => 'FREE100'
        ]
    ];
    
    if ($random < 50) {
        $prize = $prizes['lose'];
    } elseif ($random < 80) {
        $prize = $prizes['win50'];
    } else {
        $prize = $prizes['jackpot'];
    }
    
    $segments = $prize['segments'];
    $segment = $segments[array_rand($segments)];
    
    return [
        'segment' => $segment,
        'title' => $prize['title'],
        'message' => $prize['message'],
        'icon' => $prize['icon'],
        'type' => $prize['type'],
        'code' => $prize['code']
    ];
}

$showPopup = isset($_SESSION['show_popup']) && $_SESSION['show_popup'];
$result = isset($_SESSION['result']) ? $_SESSION['result'] : null;

// Calculate rotation
$rotation = 0;
if ($result) {
    $segmentAngle = 360 / 6;
    $targetAngle = 360 - ($result['segment'] * $segmentAngle + $segmentAngle / 2);
    $spins = rand(5, 8);
    $rotation = ($spins * 360) + $targetAngle;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Spin & Win - Premium Edition</title>
  <style>
    * { 
      margin: 0; 
      padding: 0; 
      box-sizing: border-box; 
    }
    
    body {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      overflow: hidden;
      position: relative;
    }

    body::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: 
        radial-gradient(circle at 20% 50%, rgba(120, 40, 200, 0.15) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(255, 100, 200, 0.15) 0%, transparent 50%),
        radial-gradient(circle at 40% 20%, rgba(40, 180, 240, 0.15) 0%, transparent 50%);
      animation: bgShift 15s ease infinite;
      pointer-events: none;
    }

    @keyframes bgShift {
      0%, 100% { opacity: 1; transform: scale(1); }
      50% { opacity: 0.8; transform: scale(1.1); }
    }

    h1 {
      color: #fff;
      font-size: 3rem;
      margin-bottom: 40px;
      text-shadow: 
        0 0 30px rgba(255,255,255,0.4), 
        0 0 60px rgba(138, 43, 226, 0.4);
      text-align: center;
      letter-spacing: 3px;
      font-weight: 700;
      animation: titleGlow 3s ease-in-out infinite alternate;
      position: relative;
      z-index: 1;
    }

    @keyframes titleGlow {
      from { 
        text-shadow: 
          0 0 30px rgba(255,255,255,0.4), 
          0 0 60px rgba(138, 43, 226, 0.4); 
      }
      to { 
        text-shadow: 
          0 0 40px rgba(255,255,255,0.6), 
          0 0 80px rgba(138, 43, 226, 0.6); 
      }
    }

    .wheel-container {
      position: relative;
      width: 400px;
      height: 400px;
      filter: drop-shadow(0 20px 60px rgba(0, 0, 0, 0.5));
      animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-15px); }
    }

    .pointer {
      position: absolute;
      top: -15px;
      left: 50%;
      transform: translateX(-50%);
      width: 0;
      height: 0;
      border-left: 24px solid transparent;
      border-right: 24px solid transparent;
      border-top: 50px solid #FFD700;
      z-index: 10;
      filter: drop-shadow(0 8px 15px rgba(255, 215, 0, 0.8));
      animation: pointerPulse 2s ease-in-out infinite;
    }

    @keyframes pointerPulse {
      0%, 100% { transform: translateX(-50%) scale(1); }
      50% { transform: translateX(-50%) scale(1.1); }
    }

    .wheel {
      width: 100%;
      height: 100%;
      border-radius: 50%;
      position: relative;
      box-shadow: 
        0 0 40px rgba(255, 215, 0, 0.6), 
        inset 0 0 30px rgba(0, 0, 0, 0.4),
        0 0 80px rgba(138, 43, 226, 0.3);
      border: 8px solid #FFD700;
      transition: transform 5s cubic-bezier(0.17, 0.67, 0.05, 0.99);
      background: #1a1a2e;
      overflow: hidden;
      transform: rotate(<?php echo $rotation; ?>deg);
    }

    .wheel-inner {
      position: absolute;
      width: 100%;
      height: 100%;
      border-radius: 50%;
    }

    .segment {
      position: absolute;
      width: 50%;
      height: 50%;
      top: 0;
      left: 50%;
      transform-origin: 0% 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%);
      overflow: hidden;
      transition: filter 0.3s ease;
    }

    .segment:hover {
      filter: brightness(1.15);
    }

    .segment:nth-child(1) { 
      transform: rotate(-60deg) skewY(-30deg); 
      background: linear-gradient(135deg, #FF6B6B 0%, #FF5252 100%);
    }
    .segment:nth-child(2) { 
      transform: rotate(0deg) skewY(-30deg); 
      background: linear-gradient(135deg, #F38181 0%, #E57373 100%);
    }
    .segment:nth-child(3) { 
      transform: rotate(60deg) skewY(-30deg); 
      background: linear-gradient(135deg, #4ECDC4 0%, #26B5AC 100%);
    }
    .segment:nth-child(4) { 
      transform: rotate(120deg) skewY(-30deg); 
      background: linear-gradient(135deg, #FFE66D 0%, #FFD93D 100%);
    }
    .segment:nth-child(5) { 
      transform: rotate(180deg) skewY(-30deg); 
      background: linear-gradient(135deg, #DDA0DD 0%, #D391D3 100%);
    }
    .segment:nth-child(6) { 
      transform: rotate(240deg) skewY(-30deg); 
      background: linear-gradient(135deg, #95E1D3 0%, #7DD3C0 100%);
    }

    .text-overlay {
      position: absolute;
      width: 100%;
      height: 100%;
      top: 0;
      left: 0;
      pointer-events: none;
    }

    .label {
      position: absolute;
      top: 50%;
      left: 50%;
      width: 120px;
      height: 35px;
      transform-origin: 0 50%;
      display: flex;
      align-items: center;
      justify-content: flex-end;
      padding-right: 20px;
    }

    .label span {
      color: #1a1a2e;
      font-weight: 800;
      font-size: 13px;
      transform: rotate(0deg);
      text-shadow: 
        1px 1px 3px rgba(255,255,255,0.5),
        -1px -1px 3px rgba(255,255,255,0.3);
      white-space: nowrap;
      letter-spacing: 0.5px;
    }

    .label:nth-child(1) { transform: rotate(-60deg) translateX(60px); }
    .label:nth-child(2) { transform: rotate(0deg) translateX(60px); }
    .label:nth-child(3) { transform: rotate(60deg) translateX(60px); }
    .label:nth-child(4) { transform: rotate(120deg) translateX(60px); }
    .label:nth-child(5) { transform: rotate(180deg) translateX(60px); }
    .label:nth-child(6) { transform: rotate(240deg) translateX(60px); }

    .center-circle {
      position: absolute;
      width: 70px;
      height: 70px;
      background: linear-gradient(145deg, #FFD700, #FFA500);
      border-radius: 50%;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      z-index: 5;
      box-shadow: 
        0 6px 20px rgba(0,0,0,0.4),
        inset 0 -3px 10px rgba(0,0,0,0.2),
        inset 0 3px 10px rgba(255,255,255,0.3);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 28px;
      border: 4px solid rgba(255, 215, 0, 0.5);
    }

    .spin-btn {
      margin-top: 40px;
      padding: 18px 70px;
      font-size: 1.3rem;
      font-weight: 800;
      color: #1a1a2e;
      background: linear-gradient(145deg, #FFD700, #FFA500);
      border: none;
      border-radius: 50px;
      cursor: pointer;
      box-shadow: 
        0 8px 25px rgba(255, 215, 0, 0.5),
        inset 0 -3px 10px rgba(0, 0, 0, 0.2),
        inset 0 3px 10px rgba(255, 255, 255, 0.3);
      transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
      text-transform: uppercase;
      letter-spacing: 3px;
      position: relative;
      overflow: hidden;
      z-index: 1;
    }

    .spin-btn::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 0;
      height: 0;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.3);
      transform: translate(-50%, -50%);
      transition: width 0.6s ease, height 0.6s ease;
    }

    .spin-btn:hover::before {
      width: 300px;
      height: 300px;
    }

    .spin-btn:hover {
      transform: translateY(-5px) scale(1.05);
      box-shadow: 
        0 15px 40px rgba(255, 215, 0, 0.7),
        inset 0 -3px 10px rgba(0, 0, 0, 0.2),
        inset 0 3px 10px rgba(255, 255, 255, 0.3);
    }

    .spin-btn:active {
      transform: translateY(-2px) scale(1.02);
    }

    .popup-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.9);
      backdrop-filter: blur(10px);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 100;
    }

    .popup-overlay.show {
      display: flex;
      animation: fadeIn 0.4s ease;
    }

    .popup {
      background: linear-gradient(145deg, #1e3a5f, #16213e);
      padding: 50px 60px;
      border-radius: 30px;
      text-align: center;
      box-shadow: 
        0 30px 80px rgba(0, 0, 0, 0.6),
        inset 0 1px 30px rgba(255, 255, 255, 0.1);
      border: 4px solid #FFD700;
      animation: popIn 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
      max-width: 90%;
      position: relative;
      overflow: hidden;
    }

    .popup::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: linear-gradient(
        45deg,
        transparent 30%,
        rgba(255, 255, 255, 0.05) 50%,
        transparent 70%
      );
      animation: shimmer 3s infinite;
    }

    @keyframes shimmer {
      0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
      100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
    }

    .popup.winner { border-color: #4ECDC4; }

    .popup.jackpot {
      border-color: #FFD700;
      animation: popIn 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55), 
                 glow 1.5s ease-in-out infinite alternate;
    }

    @keyframes glow {
      from { 
        box-shadow: 
          0 0 30px #FFD700, 
          0 0 60px #FFD700,
          inset 0 1px 30px rgba(255, 255, 255, 0.1); 
      }
      to { 
        box-shadow: 
          0 0 50px #FFD700, 
          0 0 100px #FFD700,
          inset 0 1px 30px rgba(255, 255, 255, 0.1); 
      }
    }

    .popup-icon { 
      font-size: 6rem; 
      margin-bottom: 20px;
      animation: iconBounce 0.8s ease;
      position: relative;
      z-index: 1;
    }

    @keyframes iconBounce {
      0%, 100% { transform: scale(1); }
      25% { transform: scale(1.2) rotate(-10deg); }
      50% { transform: scale(1.1) rotate(10deg); }
      75% { transform: scale(1.15) rotate(-5deg); }
    }
    
    .popup h2 {
      color: #FFD700;
      font-size: 2.2rem;
      margin-bottom: 15px;
      text-shadow: 0 0 20px rgba(255, 215, 0, 0.5);
      position: relative;
      z-index: 1;
      letter-spacing: 1px;
    }

    .popup.winner h2 { 
      color: #4ECDC4;
      text-shadow: 0 0 20px rgba(78, 205, 196, 0.5);
    }
    .popup.jackpot h2 { 
      color: #FFD700;
      animation: textGlow 1s ease-in-out infinite alternate;
    }
    .popup.loser h2 { 
      color: #FF6B6B;
      text-shadow: 0 0 20px rgba(255, 107, 107, 0.5);
    }

    @keyframes textGlow {
      from { text-shadow: 0 0 20px rgba(255, 215, 0, 0.5); }
      to { text-shadow: 0 0 30px rgba(255, 215, 0, 0.8); }
    }

    .popup p {
      color: #fff;
      font-size: 1.15rem;
      margin-bottom: 30px;
      line-height: 1.6;
      position: relative;
      z-index: 1;
    }

    .discount-code {
      background: rgba(255, 255, 255, 0.15);
      padding: 15px 30px;
      border-radius: 15px;
      margin-bottom: 25px;
      display: inline-block;
      backdrop-filter: blur(10px);
      border: 2px solid rgba(255, 255, 255, 0.2);
      position: relative;
      z-index: 1;
      transition: all 0.3s ease;
    }

    .discount-code:hover {
      transform: scale(1.05);
      background: rgba(255, 255, 255, 0.2);
    }

    .discount-code span {
      color: #4ECDC4;
      font-size: 1.8rem;
      font-weight: 800;
      letter-spacing: 4px;
      text-shadow: 0 0 10px rgba(78, 205, 196, 0.5);
    }

    .popup-btn {
      padding: 15px 50px;
      font-size: 1.1rem;
      font-weight: 800;
      color: #1a1a2e;
      background: linear-gradient(145deg, #4ECDC4, #3dbdb5);
      border: none;
      border-radius: 30px;
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
      text-transform: uppercase;
      letter-spacing: 2px;
      box-shadow: 0 6px 20px rgba(78, 205, 196, 0.4);
      position: relative;
      z-index: 1;
      text-decoration: none;
      display: inline-block;
    }

    .popup-btn:hover {
      transform: scale(1.1);
      box-shadow: 0 8px 30px rgba(78, 205, 196, 0.6);
    }

    .popup-btn:active {
      transform: scale(1.05);
    }

    @keyframes fadeIn { 
      from { opacity: 0; } 
      to { opacity: 1; } 
    }
    
    @keyframes popIn { 
      from { 
        transform: scale(0.3) rotate(-10deg); 
        opacity: 0; 
      } 
      to { 
        transform: scale(1) rotate(0deg); 
        opacity: 1; 
      } 
    }

    .confetti {
      position: fixed;
      width: 12px;
      height: 12px;
      top: -20px;
      animation: fall 4s linear forwards;
      z-index: 1000;
    }

    @keyframes fall {
      to {
        transform: translateY(100vh) rotate(720deg);
        opacity: 0;
      }
    }

    /* Confetti colors */
    <?php if ($showPopup && $result && $result['code']): ?>
      <?php 
      $colors = ['#FF6B6B', '#4ECDC4', '#FFE66D', '#95E1D3', '#DDA0DD', '#F38181', '#FFD700', '#FF69B4', '#87CEEB'];
      for ($i = 0; $i < 80; $i++): 
        $color = $colors[array_rand($colors)];
        $left = rand(0, 100);
        $size = rand(6, 18);
        $duration = rand(3, 5);
        $delay = $i * 0.025;
        $borderRadius = rand(0, 1) ? '50%' : '0';
      ?>
      .confetti-<?php echo $i; ?> {
        left: <?php echo $left; ?>vw;
        background: <?php echo $color; ?>;
        width: <?php echo $size; ?>px;
        height: <?php echo $size; ?>px;
        border-radius: <?php echo $borderRadius; ?>;
        animation-duration: <?php echo $duration; ?>s;
        animation-delay: <?php echo $delay; ?>s;
      }
      <?php endfor; ?>
    <?php endif; ?>

    /* Responsive Design */
    @media (max-width: 768px) {
      h1 {
        font-size: 2rem;
        margin-bottom: 25px;
        letter-spacing: 2px;
      }

      .wheel-container {
        width: 300px;
        height: 300px;
      }

      .pointer {
        border-left: 18px solid transparent;
        border-right: 18px solid transparent;
        border-top: 38px solid #FFD700;
      }

      .wheel {
        border: 6px solid #FFD700;
      }

      .label {
        width: 90px;
        padding-right: 15px;
      }

      .label span {
        font-size: 10px;
      }

      .label:nth-child(1) { transform: rotate(-60deg) translateX(45px); }
      .label:nth-child(2) { transform: rotate(0deg) translateX(45px); }
      .label:nth-child(3) { transform: rotate(60deg) translateX(45px); }
      .label:nth-child(4) { transform: rotate(120deg) translateX(45px); }
      .label:nth-child(5) { transform: rotate(180deg) translateX(45px); }
      .label:nth-child(6) { transform: rotate(240deg) translateX(45px); }

      .center-circle {
        width: 55px;
        height: 55px;
        font-size: 22px;
        border: 3px solid rgba(255, 215, 0, 0.5);
      }

      .spin-btn {
        padding: 14px 50px;
        font-size: 1.1rem;
        letter-spacing: 2px;
        margin-top: 30px;
      }

      .popup {
        padding: 35px 40px;
        border-radius: 20px;
        border: 3px solid #FFD700;
        max-width: 85%;
      }

      .popup-icon {
        font-size: 4.5rem;
        margin-bottom: 15px;
      }

      .popup h2 {
        font-size: 1.6rem;
        margin-bottom: 12px;
      }

      .popup p {
        font-size: 1rem;
        margin-bottom: 25px;
      }

      .discount-code {
        padding: 12px 25px;
        margin-bottom: 20px;
      }

      .discount-code span {
        font-size: 1.4rem;
        letter-spacing: 3px;
      }

      .popup-btn {
        padding: 12px 40px;
        font-size: 1rem;
        letter-spacing: 1.5px;
      }
    }

    @media (max-width: 480px) {
      h1 {
        font-size: 1.6rem;
        margin-bottom: 20px;
        letter-spacing: 1.5px;
      }

      .wheel-container {
        width: 260px;
        height: 260px;
      }

      .pointer {
        border-left: 16px solid transparent;
        border-right: 16px solid transparent;
        border-top: 32px solid #FFD700;
        top: -12px;
      }

      .wheel {
        border: 5px solid #FFD700;
      }

      .label {
        width: 75px;
        padding-right: 12px;
      }

      .label span {
        font-size: 8.5px;
        letter-spacing: 0.3px;
      }

      .label:nth-child(1) { transform: rotate(-60deg) translateX(38px); }
      .label:nth-child(2) { transform: rotate(0deg) translateX(38px); }
      .label:nth-child(3) { transform: rotate(60deg) translateX(38px); }
      .label:nth-child(4) { transform: rotate(120deg) translateX(38px); }
      .label:nth-child(5) { transform: rotate(180deg) translateX(38px); }
      .label:nth-child(6) { transform: rotate(240deg) translateX(38px); }

      .center-circle {
        width: 50px;
        height: 50px;
        font-size: 20px;
        border: 3px solid rgba(255, 215, 0, 0.5);
      }

      .spin-btn {
        padding: 12px 40px;
        font-size: 1rem;
        letter-spacing: 1.5px;
        margin-top: 25px;
      }

      .popup {
        padding: 30px 25px;
        border-radius: 15px;
        max-width: 90%;
      }

      .popup-icon {
        font-size: 3.5rem;
        margin-bottom: 12px;
      }

      .popup h2 {
        font-size: 1.3rem;
        margin-bottom: 10px;
      }

      .popup p {
        font-size: 0.9rem;
        margin-bottom: 20px;
        line-height: 1.5;
      }

      .discount-code {
        padding: 10px 20px;
        margin-bottom: 18px;
      }

      .discount-code span {
        font-size: 1.2rem;
        letter-spacing: 2px;
      }

      .popup-btn {
        padding: 10px 35px;
        font-size: 0.95rem;
        letter-spacing: 1.5px;
      }
    }

    @media (max-width: 360px) {
      h1 {
        font-size: 1.4rem;
        margin-bottom: 15px;
      }

      .wheel-container {
        width: 240px;
        height: 240px;
      }

      .label span {
        font-size: 7.5px;
      }

      .popup h2 {
        font-size: 1.1rem;
      }

      .popup p {
        font-size: 0.85rem;
      }

      .discount-code span {
        font-size: 1.1rem;
      }
    }
  </style>
</head>
<body>
  <h1>✨ Spin & Win! ✨</h1>
  
  <form method="POST" action="">
    <div class="wheel-container">
      <div class="pointer"></div>
      <div class="wheel">
        <div class="wheel-inner">
          <div class="segment"></div>
          <div class="segment"></div>
          <div class="segment"></div>
          <div class="segment"></div>
          <div class="segment"></div>
          <div class="segment"></div>
        </div>
        <div class="text-overlay">
          <div class="label"><span>BETTER LUCK</span></div>
          <div class="label"><span>50% OFF</span></div>
          <div class="label"><span>BETTER LUCK</span></div>
          <div class="label"><span>100% OFF</span></div>
          <div class="label"><span>BETTER LUCK</span></div>
          <div class="label"><span>50% OFF</span></div>
        </div>
      </div>
      <div class="center-circle">🎯</div>
    </div>

    <button type="submit" name="spin" class="spin-btn">SPIN</button>
  </form>
  
  <?php if ($showPopup && $result): ?>
  <div class="popup-overlay show">
    <div class="popup <?php echo htmlspecialchars($result['type']); ?>">
      <div class="popup-icon"><?php echo $result['icon']; ?></div>
      <h2><?php echo htmlspecialchars($result['title']); ?></h2>
      <p><?php echo htmlspecialchars($result['message']); ?></p>
      
      <?php if ($result['code']): ?>
      <div class="discount-code">
        <span><?php echo htmlspecialchars($result['code']); ?></span>
      </div>
      <?php endif; ?>
      
      <a href="?close=1" class="popup-btn">Play Again</a>
    </div>
  </div>
  
  <!-- Confetti -->
  <?php if ($result['code']): ?>
    <?php for ($i = 0; $i < 80; $i++): ?>
    <div class="confetti confetti-<?php echo $i; ?>"></div>
    <?php endfor; ?>
  <?php endif; ?>
  <?php endif; ?>

</body>
</html>