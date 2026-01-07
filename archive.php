<?php
require_once 'post_operations.php';

$conn = connectDB();
$year_filter = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$category_filter = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

$sql = "SELECT * FROM posts WHERE YEAR(post_date) = ? AND status = 'active'";
$params = [$year_filter];
$types = "i";

if ($category_filter) {
    if ($category_filter === 'achievement') {
        $sql .= " AND (category = 'achievement' OR category = 'achievement_event')";
    } elseif ($category_filter === 'event') {
        $sql .= " AND (category = 'event' OR category = 'achievement_event')";
    } else {
        $sql .= " AND category = ?";
        $params[] = $category_filter;
        $types .= "s";
    }
}
if ($search) {
    $sql .= " AND (title LIKE ? OR description LIKE ? OR post_date LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "sss";
}
$sql .= " ORDER BY post_date DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$posts = mysqli_fetch_all($result, MYSQLI_ASSOC);

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARCHIVE HISTORY | D'MARSIANS TAEKWONDO GYM</title>
    <link rel="stylesheet" href="Styles/webpage.css">
    <link rel="stylesheet" href="Styles/archive.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="archive-page">
    <a href="webpage.php" class="archive-back-btn" title="Back to Home" aria-label="Back to Home">
        <i class="fa-solid fa-arrow-left"></i>
    </a>

    <main class="archive-shell">
        <div class="container">
            <h1 class="archive-title">ARCHIVE HISTORY</h1>

            <form class="archive-controls" id="archiveFilterForm" method="get" autocomplete="off">
                <div class="filter-deck">
                    <div class="filter-field">
                        <label for="archiveCategory">Category</label>
                        <select id="archiveCategory" name="category">
                            <option value="" <?= $category_filter == '' ? 'selected' : '' ?>>All</option>
                            <option value="achievement" <?= $category_filter == 'achievement' ? 'selected' : '' ?>>Achievement</option>
                            <option value="event" <?= $category_filter == 'event' ? 'selected' : '' ?>>Event</option>
                        </select>
                    </div>
                    <div class="filter-divider"></div>
                    <div class="filter-field">
                        <label for="archiveYear">Year</label>
                        <select id="archiveYear" name="year">
                            <?php
                            $currentYear = date('Y');
                            for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
                                echo "<option value=\"$y\"".($year_filter == $y ? ' selected' : '').">$y</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="filter-divider"></div>
                    <div class="filter-field filter-search">
                        <label class="visually-hidden" for="archiveSearch">Search</label>
                        <input id="archiveSearch" type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by title or date">
                        <button class="filter-search-btn" type="submit" aria-label="Search">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                </div>
            </form>

            <section class="archive-grid mt-4">
                <div class="row g-4 justify-content-center" id="archiveGrid">
                    <?php if (empty($posts)): ?>
                        <div class="text-center w-100" style="color:#111;">No posts found.</div>
                    <?php else: ?>
                        <?php foreach ($posts as $i => $post): ?>
                            <div class="col-12 col-sm-6 col-lg-4 d-flex">
                                <article class="archive-card w-100" style="--i: <?= (int)$i ?>;"
                                         data-title="<?= htmlspecialchars($post['title']) ?>"
                                         data-desc="<?= htmlspecialchars($post['description']) ?>"
                                         data-date="<?= date('F j, Y g:i A', strtotime($post['post_date'])) ?>"
                                         data-image="<?= !empty($post['image_path']) ? htmlspecialchars($post['image_path']) : 'https://via.placeholder.com/400x300.png/2d2d2d/ffffff?text=No+Image' ?>">
                                    <div class="archive-media">
                                        <img class="img-fluid w-100"
                                             src="<?= !empty($post['image_path']) ? htmlspecialchars($post['image_path']) : 'https://via.placeholder.com/400x300.png/2d2d2d/ffffff?text=No+Image' ?>"
                                             alt="<?= htmlspecialchars($post['title']) ?>">
                                        <div class="archive-date-tag"><?= date('M j, Y', strtotime($post['post_date'])) ?></div>
                                    </div>
                                    <div class="archive-body">
                                        <div class="archive-title-row">
                                            <h3 class="archive-post-title"><?= htmlspecialchars($post['title']) ?></h3>
                                            <span class="archive-pill">
                                                <?= $post['category'] === 'achievement_event' ? 'A/E' : strtoupper($post['category']) ?>
                                            </span>
                                        </div>
                                        <p class="archive-desc"><?= htmlspecialchars($post['description']) ?></p>
                                    </div>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>
    <!-- <button class="load-more-btn">LOAD MORE</button> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    (function () {
      const form = document.getElementById('archiveFilterForm');
      if (!form) return;

      const deck = form.querySelector('.filter-deck');
      const category = document.getElementById('archiveCategory');
      const year = document.getElementById('archiveYear');
      const search = document.getElementById('archiveSearch');

      const reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      const submitWithShuffle = () => {
        if (reduced) return form.submit();
        document.body.classList.add('is-filtering');
        window.setTimeout(() => form.submit(), 180);
      };

      category?.addEventListener('change', submitWithShuffle);
      year?.addEventListener('change', submitWithShuffle);

      form.addEventListener('submit', (e) => {
        if (reduced) return;
        if (document.body.classList.contains('is-filtering')) return;
        e.preventDefault();
        submitWithShuffle();
      });

      // "Active scanning" feel while typing
      let t;
      search?.addEventListener('input', () => {
        if (!deck) return;
        deck.classList.add('is-typing');
        window.clearTimeout(t);
        t = window.setTimeout(() => deck.classList.remove('is-typing'), 350);
      });
    })();
    </script>
    
    <!-- Post Details Modal -->
    <div class="postmodal-overlay" id="postModal" aria-hidden="true">
        <div class="postmodal-dialog" role="dialog" aria-modal="true" aria-labelledby="postModalTitle">
            <button class="postmodal-close" type="button" aria-label="Close" id="postModalClose">&times;</button>
            <div class="postmodal-body">
                <div class="postmodal-image">
                    <img id="postModalImg" alt="Post image">
                </div>
                <div class="postmodal-content">
                    <h3 id="postModalTitle"></h3>
                    <p class="postmodal-meta" id="postModalDate"></p>
                    <div class="postmodal-desc" id="postModalDesc"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('postModal');
        const modalImg = document.getElementById('postModalImg');
        const modalTitle = document.getElementById('postModalTitle');
        const modalDate = document.getElementById('postModalDate');
        const modalDesc = document.getElementById('postModalDesc');
        const closeBtn = document.getElementById('postModalClose');
        
        // Open Modal
        document.querySelectorAll('.archive-card').forEach(card => {
            card.addEventListener('click', () => {
                const title = card.getAttribute('data-title');
                const desc = card.getAttribute('data-desc');
                const date = card.getAttribute('data-date');
                const image = card.getAttribute('data-image');
                
                modalTitle.textContent = title;
                modalDesc.textContent = desc;
                modalDate.textContent = date;
                modalImg.src = image;
                
                modal.classList.add('open');
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden'; // Prevent scrolling
            });
        });
        
        // Close Modal Function
        const closeModal = () => {
            modal.classList.remove('open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        };
        
        // Event Listeners
        closeBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.classList.contains('open')) closeModal();
        });
    });
    </script>
</body>
</html> 