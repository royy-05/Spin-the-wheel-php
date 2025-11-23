let isSpinning = false;

async function spinWheel() {
    if (isSpinning) return;

    isSpinning = true;
    const spinButton = document.getElementById('spinButton');
    const wheel = document.getElementById('wheel');

    spinButton.disabled = true;
    spinButton.textContent = '🎪 SPINNING...';

    try {
        const response = await fetch('spin.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();
        if (!data.success) {
            alert(data.error || 'An error occurred. Please try again.');
            isSpinning = false;
            spinButton.disabled = false;
            spinButton.textContent = '🎯 SPIN NOW';
            return;
        }

        const segmentIndex = data.segmentIndex;
        const prize = data.prize;

        const segmentAngle = 360 / 6;
        const targetRotation = (segmentIndex * segmentAngle) + (segmentAngle / 2);
        const spins = 5;
        const totalRotation = (spins * 360) + (360 - targetRotation);
        wheel.classList.add('spinning');
        wheel.style.transform = `rotate(${totalRotation}deg)`;
        setTimeout(() => {
            showResult(prize);
            isSpinning = false;
            spinButton.disabled = false;
            spinButton.textContent = '🎯 SPIN NOW';
            wheel.classList.remove('spinning');
        }, 4000);

    } catch (error) {
        console.error('Error:', error);
        alert('Failed to connect to server. Please try again.');
        isSpinning = false;
        spinButton.disabled = false;
        spinButton.textContent = '🎯 SPIN NOW';
    }
}

function showResult(prize) {
    const modal = document.getElementById('resultModal');
    const icon = document.getElementById('resultIcon');
    const title = document.getElementById('resultTitle');
    const message = document.getElementById('resultMessage');

    icon.textContent = prize.icon;

    if (prize.name === "Better Luck Next Time") {
        title.textContent = "Better Luck Next Time!";
        message.textContent = "Don't give up! Try spinning again for another chance to win!";
    } else if (prize.name === "50% OFF") {
        title.textContent = "🎊 Congratulations! 🎊";
        message.textContent = "You won 50% OFF on your next purchase!";
        createConfetti();
    } else {
        title.textContent = "🏆 JACKPOT! 🏆";
        message.textContent = "Amazing! You won 100% OFF - It's FREE!";
        createConfetti();
    }

    modal.classList.add('show');
}

function closeModal() {
    const modal = document.getElementById('resultModal');
    modal.classList.remove('show');

    const wheel = document.getElementById('wheel');
    wheel.style.transition = 'none';
    wheel.style.transform = 'rotate(0deg)';
    setTimeout(() => {
        wheel.style.transition = 'transform 4s cubic-bezier(0.25, 0.1, 0.25, 1)';
    }, 50);
}

function createConfetti() {
    const colors = ['#ffd700', '#ff6b6b', '#4ecdc4', '#ffd93d', '#a8e6cf', '#ff8c94'];

    for (let i = 0; i < 50; i++) {
        setTimeout(() => {
            const confetti = document.createElement('div');
            confetti.className = 'confetti';
            confetti.style.left = Math.random() * 100 + '%';
            confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
            confetti.style.animationDelay = Math.random() * 0.5 + 's';
            document.body.appendChild(confetti);

            setTimeout(() => confetti.remove(), 3000);
        }, i * 30);
    }
}
document.getElementById('resultModal').addEventListener('click', function (e) {
    if (e.target === this) {
        closeModal();
    }
});