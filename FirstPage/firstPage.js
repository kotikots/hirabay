const track = document.getElementById("carouselTrack");
const cards = document.querySelectorAll(".card");
const prevBtn = document.getElementById("prevBtn");
const nextBtn = document.getElementById("nextBtn");

const cardWidth = cards[0].offsetWidth + 30; // card + margins
let index = 1; // Start at real 1
let isTransitioning = false;

function goToIndex(newIndex) {
  if (!isTransitioning) {
    track.style.transition = "transform 0.3s ease-in-out";
    track.style.transform = `translateX(-${newIndex * cardWidth}px)`;
    updateCurrentCard(newIndex);
  }
}

function updateCurrentCard(activeIndex) {
  cards.forEach(card => card.classList.remove('current'));

  if (activeIndex === 0) {
    cards[cards.length - 2].classList.add('current');
  } else if (activeIndex === cards.length - 1) {
    cards[1].classList.add('current');
  } else {
    cards[activeIndex].classList.add('current');
  }
}

window.addEventListener('load', () => {
  goToIndex(index);
});

nextBtn.addEventListener('click', () => {
  if (isTransitioning) return;
  index++;
  goToIndex(index);
});

prevBtn.addEventListener('click', () => {
  if (isTransitioning) return;
  index--;
  goToIndex(index);
});

track.addEventListener('transitionstart', () => {
  isTransitioning = true;
});

track.addEventListener('transitionend', () => {
  isTransitioning = false;
  
  if (index === cards.length - 1) {
    track.style.transition = 'none';
    index = 1;
    track.style.transform = `translateX(-${index * cardWidth}px)`;
    updateCurrentCard(index);
    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        track.style.transition = 'transform 0.4s ease-in-out';
      });
    });
  }

  if (index === 0) {
    track.style.transition = 'none';
    index = cards.length - 2;
    track.style.transform = `translateX(-${index * cardWidth}px)`;
    updateCurrentCard(index);
    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        track.style.transition = 'transform 0.4s ease-in-out';
      });
    });
  }
});

const learnMoreBtn = document.getElementById('learnMoreBtn');
const infoSection = document.getElementById('info-section');

learnMoreBtn.addEventListener('click', () => {
  infoSection.scrollIntoView({
    behavior: 'smooth',
    block: 'center'
  });
});