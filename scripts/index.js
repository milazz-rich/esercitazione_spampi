const searchInput = document.querySelector("#movie-search");
const movieCards = Array.from(document.querySelectorAll(".movie-card"));

function applyThemeFromStorage() {
  const savedTheme = localStorage.getItem("moviehub-theme");
  if (savedTheme === "dark") {
    document.body.classList.add("dark");
  }
}

function initThemeToggle() {
  const btn = document.querySelector("#theme-toggle");
  if (!btn) return;
  btn.addEventListener("click", () => {
    document.body.classList.toggle("dark");
    localStorage.setItem("moviehub-theme", document.body.classList.contains("dark") ? "dark" : "light");
  });
}

async function addLike(movieId, likesLabel) {
  const fd = new FormData();
  fd.append("movie_id", String(movieId));
  const response = await fetch("api/api_like.php", { method: "POST", body: fd });
  const data = await response.json();
  if (!response.ok || !data.ok) throw new Error(data.error || "Errore aggiornamento like");
  likesLabel.textContent = `${Number(data.likes)} likes`;
}

function filterMovies() {
  if (!searchInput) return;
  const query = searchInput.value.trim().toLowerCase();
  movieCards.forEach((card) => {
    const title = card.dataset.title || "";
    const plot = card.dataset.plot || "";
    card.style.display = title.includes(query) || plot.includes(query) ? "" : "none";
  });
}

applyThemeFromStorage();
initThemeToggle();

if (searchInput) {
  searchInput.addEventListener("input", filterMovies);
}

document.querySelectorAll(".like-btn").forEach((btn) => {
  btn.addEventListener("click", () => {
    const card = btn.closest(".movie-card");
    const likesLabel = card ? card.querySelector("[data-likes-label]") : null;
    if (!likesLabel) return;
    addLike(btn.dataset.movieId, likesLabel).catch(() => {
      likesLabel.textContent = likesLabel.textContent;
    });
  });
});
