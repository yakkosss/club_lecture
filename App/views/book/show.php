<?php
/**
 * @var array  $book
 * @var array  $documents
 * @var array  $reviews
 * @var array  $comments
 * @var array|null $myReview
 * @var array|null $myProgress
 * @var float|null $avgNote
 * @var float|null $avgProgress
 * @var bool   $canMod
 */
$pageTitle  = $book['title'];
$user       = $_SESSION['user'] ?? null;
$canEdit    = $user && in_array($user['role'], ['admin', 'moderator'], true);
$canDelete  = $user && $user['role'] === 'admin';
$canMod     = $user && in_array($user['role'], ['admin', 'moderator'], true);

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

if (!function_exists('club_format_bytes')) {
    function club_format_bytes(int $bytes): string {
        if ($bytes < 1024) return $bytes . ' o';
        if ($bytes < 1024 * 1024) return round($bytes / 1024, 1) . ' Ko';
        return round($bytes / 1024 / 1024, 1) . ' Mo';
    }
}
if (!function_exists('club_stars')) {
    function club_stars(int $note): string {
        return str_repeat('★', $note) . str_repeat('☆', 5 - $note);
    }
}

ob_start();
?>
<div style="margin-bottom: 18px;">
    <a href="<?= BASE_URL ?>index.php?controller=Book&action=index" style="color: var(--color-text-muted);">← Retour aux lectures</a>
</div>

<?php if ($flashSuccess): ?>
    <div class="alert alert--success"><?= htmlspecialchars($flashSuccess) ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
    <div class="alert alert--error"><?= htmlspecialchars($flashError) ?></div>
<?php endif; ?>

<!-- Fiche livre -->
<div class="card">
    <div style="display:grid; grid-template-columns: 220px 1fr; gap: 24px;" class="book-show">
        <div>
            <div style="aspect-ratio: 3/4; background: var(--color-bg-soft); border-radius: var(--radius); overflow: hidden; display: flex; align-items: center; justify-content: center;">
                <?php if (!empty($book['cover_path'])): ?>
                    <img src="<?= BASE_URL . htmlspecialchars($book['cover_path']) ?>" alt=""
                         style="width:100%; height:100%; object-fit:cover;">
                <?php else: ?>
                    <span style="color: var(--color-text-muted); font-size: 14px;">Pas de couverture</span>
                <?php endif; ?>
            </div>
            <?php if ($avgNote !== null): ?>
                <div style="text-align:center; margin-top:10px; color:var(--color-primary); font-size:20px;" title="Note moyenne : <?= $avgNote ?>/5">
                    <?= club_stars((int) round($avgNote)) ?>
                    <div style="font-size:13px; color:var(--color-text-muted);"><?= $avgNote ?>/5</div>
                </div>
            <?php endif; ?>
            <?php if ($avgProgress !== null): ?>
                <div style="margin-top:10px; text-align:center;">
                    <div style="font-size:13px; color:var(--color-text-muted); margin-bottom:4px;">Progression moyenne</div>
                    <div class="progress-bar"><div class="progress-bar__fill" style="width:<?= $avgProgress ?>%"></div></div>
                    <div style="font-size:13px; color:var(--color-text-muted);"><?= $avgProgress ?> %</div>
                </div>
            <?php endif; ?>
        </div>
        <div>
            <h1 class="card__title" style="margin-bottom: 4px;"><?= htmlspecialchars($book['title']) ?></h1>
            <p class="card__subtitle" style="margin-bottom: 12px;">
                par <strong><?= htmlspecialchars($book['author']) ?></strong>
                <?php if (!empty($book['release_date'])): ?>
                    · <?= htmlspecialchars(substr($book['release_date'], 0, 10)) ?>
                <?php endif; ?>
            </p>

            <?php if (!empty($book['synopsis'])): ?>
                <p style="white-space: pre-wrap;"><?= htmlspecialchars($book['synopsis']) ?></p>
            <?php else: ?>
                <p style="color: var(--color-text-muted); font-style: italic;">Pas de synopsis renseigné.</p>
            <?php endif; ?>

            <?php if (!empty($book['creator_firstname'])): ?>
                <p style="color: var(--color-text-muted); font-size: 14px; margin-top: 16px;">
                    Ajouté par <?= htmlspecialchars($book['creator_firstname'] . ' ' . $book['creator_lastname']) ?>
                </p>
            <?php endif; ?>

            <?php if ($canEdit || $canDelete): ?>
                <div style="display:flex; gap:8px; margin-top:18px; flex-wrap:wrap;">
                    <?php if ($canEdit): ?>
                        <a class="btn btn--ghost" href="<?= BASE_URL ?>index.php?controller=Book&action=displayEditForm&id=<?= (int)$book['id'] ?>">Modifier</a>
                    <?php endif; ?>
                    <?php if ($canDelete): ?>
                        <form method="POST"
                              action="<?= BASE_URL ?>index.php?controller=Book&action=deleteBook"
                              onsubmit="return confirm('Supprimer définitivement cette lecture ?');"
                              style="display:inline;">
                            <input type="hidden" name="id" value="<?= (int)$book['id'] ?>">
                            <button class="btn btn--danger" type="submit">Supprimer</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Progression personnelle -->
<?php if ($user): ?>
<div class="card" style="margin-top:18px;">
    <h2 class="card__title">Ma progression</h2>
    <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
        <div style="flex:1; min-width:200px;">
            <div class="progress-bar" style="height:14px;">
                <div class="progress-bar__fill" id="prog-fill"
                     style="width:<?= (int)($myProgress['pourcentage'] ?? 0) ?>%"></div>
            </div>
            <div style="font-size:13px; color:var(--color-text-muted); margin-top:4px;">
                <span id="prog-label"><?= (int)($myProgress['pourcentage'] ?? 0) ?></span> %
            </div>
        </div>
        <form method="POST" action="<?= BASE_URL ?>index.php?controller=Progress&action=update"
              style="display:flex; align-items:center; gap:8px;">
            <input type="hidden" name="book_id" value="<?= (int)$book['id'] ?>">
            <input type="range" name="pourcentage" id="prog-range" min="0" max="100"
                   value="<?= (int)($myProgress['pourcentage'] ?? 0) ?>"
                   style="width:140px;">
            <button class="btn btn--primary" type="submit" style="white-space:nowrap;">Enregistrer</button>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Documents PDF -->
<div class="card" style="margin-top:18px;">
    <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
        <h2 class="card__title" style="margin:0;">Documents</h2>
    </div>
    <p class="card__subtitle">PDF rattachés à cette lecture</p>

    <?php if ($canEdit): ?>
        <form action="<?= BASE_URL ?>index.php?controller=Document&action=upload"
              method="POST" enctype="multipart/form-data"
              style="display:flex; gap:8px; align-items:flex-end; flex-wrap:wrap; margin-bottom: 16px;">
            <input type="hidden" name="book_id" value="<?= (int)$book['id'] ?>">
            <div class="form__row" style="flex:1; min-width:240px;">
                <label class="form__label" for="document">Ajouter un PDF</label>
                <input class="form__input" type="file" id="document" name="document" accept="application/pdf,.pdf" required>
                <span class="form__hint">PDF uniquement · max 10 Mo</span>
            </div>
            <button class="btn btn--primary" type="submit">Uploader</button>
        </form>
    <?php endif; ?>

    <?php if (empty($documents)): ?>
        <p style="color: var(--color-text-muted); margin: 0;">Aucun document pour le moment.</p>
    <?php else: ?>
        <ul style="list-style:none; padding:0; margin:0;">
            <?php foreach ($documents as $d): ?>
                <li style="display:flex; align-items:center; justify-content:space-between; gap:12px; padding:10px 12px; border:1px solid var(--color-border); border-radius:var(--radius); margin-bottom:8px; flex-wrap:wrap;">
                    <div style="min-width:0; flex:1;">
                        <div style="font-weight:600; word-break:break-word;"><?= htmlspecialchars($d['filename']) ?></div>
                        <div style="color:var(--color-text-muted); font-size:13px;">
                            <?= club_format_bytes((int)$d['size']) ?>
                            <?php if (!empty($d['uploader_firstname'])): ?>
                                · par <?= htmlspecialchars($d['uploader_firstname'] . ' ' . $d['uploader_lastname']) ?>
                            <?php endif; ?>
                            <?php if (!empty($d['uploaded_at'])): ?>
                                · <?= htmlspecialchars(substr($d['uploaded_at'], 0, 10)) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="display:flex; gap:6px; flex-wrap:wrap;">
                        <a class="btn btn--ghost"
                           href="<?= BASE_URL ?>index.php?controller=Document&action=download&id=<?= (int)$d['id'] ?>">
                            Télécharger
                        </a>
                        <?php if ($canEdit): ?>
                            <form method="POST" action="<?= BASE_URL ?>index.php?controller=Document&action=delete"
                                  onsubmit="return confirm('Supprimer ce document ?');" style="display:inline;">
                                <input type="hidden" name="id" value="<?= (int)$d['id'] ?>">
                                <button class="btn btn--danger" type="submit">Supprimer</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<!-- Avis -->
<div class="card" style="margin-top:18px;" id="reviews">
    <h2 class="card__title">Avis</h2>

    <?php if ($user && !$myReview): ?>
        <details style="margin-bottom:16px;">
            <summary class="btn btn--ghost" style="cursor:pointer; display:inline-block;">Laisser un avis</summary>
            <form method="POST" action="<?= BASE_URL ?>index.php?controller=Review&action=store"
                  style="margin-top:12px; display:flex; flex-direction:column; gap:10px; max-width:480px;">
                <input type="hidden" name="book_id" value="<?= (int)$book['id'] ?>">
                <div class="form__row">
                    <label class="form__label">Note</label>
                    <div class="star-rating" id="star-rating-input">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star" data-val="<?= $i ?>">★</span>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="note" id="note-input" value="0" required>
                </div>
                <div class="form__row">
                    <label class="form__label" for="new-comm">Commentaire (optionnel)</label>
                    <textarea class="form__input" id="new-comm" name="commentaire" rows="3"></textarea>
                </div>
                <div><button class="btn btn--primary" type="submit">Publier</button></div>
            </form>
        </details>
    <?php elseif ($user && $myReview): ?>
        <!-- Modifier / supprimer son propre avis -->
        <details style="margin-bottom:16px;">
            <summary class="btn btn--ghost" style="cursor:pointer; display:inline-block;">Mon avis (modifier)</summary>
            <form method="POST" action="<?= BASE_URL ?>index.php?controller=Review&action=update"
                  style="margin-top:12px; display:flex; flex-direction:column; gap:10px; max-width:480px;">
                <input type="hidden" name="id" value="<?= (int)$myReview['id'] ?>">
                <div class="form__row">
                    <label class="form__label">Note</label>
                    <div class="star-rating" id="star-rating-edit">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star <?= $i <= (int)$myReview['note'] ? 'active' : '' ?>" data-val="<?= $i ?>">★</span>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="note" id="note-edit" value="<?= (int)$myReview['note'] ?>" required>
                </div>
                <div class="form__row">
                    <textarea class="form__input" name="commentaire" rows="3"><?= htmlspecialchars($myReview['commentaire'] ?? '') ?></textarea>
                </div>
                <div style="display:flex; gap:8px;">
                    <button class="btn btn--primary" type="submit">Mettre à jour</button>
                </div>
            </form>
            <form method="POST" action="<?= BASE_URL ?>index.php?controller=Review&action=delete"
                  onsubmit="return confirm('Supprimer votre avis ?');" style="margin-top:8px;">
                <input type="hidden" name="id" value="<?= (int)$myReview['id'] ?>">
                <button class="btn btn--danger" type="submit">Supprimer mon avis</button>
            </form>
        </details>
    <?php endif; ?>

    <?php if (empty($reviews)): ?>
        <p style="color:var(--color-text-muted);">Aucun avis pour le moment.</p>
    <?php else: ?>
        <?php foreach ($reviews as $r): ?>
            <?php $isOwn = $user && (int)$r['user_id'] === (int)$user['id']; ?>
            <div style="border:1px solid var(--color-border); border-radius:var(--radius); padding:12px 14px; margin-bottom:10px;
                        <?= (bool)$r['hidden'] ? 'opacity:.5; border-style:dashed;' : '' ?>">
                <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:8px;">
                    <div>
                        <span style="color:var(--color-primary); font-size:18px;"><?= club_stars((int)$r['note']) ?></span>
                        <strong style="margin-left:8px;"><?= htmlspecialchars($r['firstname'] . ' ' . $r['lastname']) ?></strong>
                        <span style="color:var(--color-text-muted); font-size:13px; margin-left:8px;"><?= htmlspecialchars(substr($r['created_at'], 0, 10)) ?></span>
                        <?php if ((bool)$r['hidden']): ?>
                            <span class="badge badge--warning" style="margin-left:6px;">Masqué</span>
                        <?php endif; ?>
                    </div>
                    <div style="display:flex; gap:6px; flex-wrap:wrap;">
                        <?php if ($canMod): ?>
                            <form method="POST" action="<?= BASE_URL ?>index.php?controller=Review&action=toggleHidden" style="display:inline;">
                                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                <button class="btn btn--ghost" type="submit" style="font-size:12px; padding:4px 8px;">
                                    <?= (bool)$r['hidden'] ? 'Réactiver' : 'Masquer' ?>
                                </button>
                            </form>
                        <?php endif; ?>
                        <?php if ($user && $user['role'] === 'admin'): ?>
                            <form method="POST" action="<?= BASE_URL ?>index.php?controller=Review&action=delete"
                                  onsubmit="return confirm('Supprimer cet avis ?');" style="display:inline;">
                                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                <button class="btn btn--danger" type="submit" style="font-size:12px; padding:4px 8px;">Supprimer</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (!empty($r['commentaire'])): ?>
                    <p style="margin:8px 0 0; white-space:pre-wrap;"><?= htmlspecialchars($r['commentaire']) ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Commentaires -->
<div class="card" style="margin-top:18px;" id="comments">
    <h2 class="card__title">Commentaires</h2>

    <?php if ($user): ?>
        <form method="POST" action="<?= BASE_URL ?>index.php?controller=Comments&action=store"
              style="margin-bottom:20px;" id="comment-form">
            <input type="hidden" name="book_id" value="<?= (int)$book['id'] ?>">
            <input type="hidden" name="parent" id="comment-parent" value="">
            <div class="form__row">
                <label class="form__label" for="comment-text">
                    Ajouter un commentaire <span id="reply-indicator" style="color:var(--color-primary); display:none;">(en réponse à #<span id="reply-id"></span>)</span>
                </label>
                <textarea class="form__input" id="comment-text" name="comment_text" rows="3" required></textarea>
            </div>
            <div style="display:flex; gap:8px; margin-top:8px;">
                <button class="btn btn--primary" type="submit">Publier</button>
                <button class="btn btn--ghost" type="button" id="cancel-reply" style="display:none;" onclick="cancelReply()">Annuler la réponse</button>
            </div>
        </form>
    <?php endif; ?>

    <?php if (empty($comments)): ?>
        <p style="color:var(--color-text-muted);">Aucun commentaire pour le moment.</p>
    <?php else: ?>
        <?php foreach ($comments as $c): ?>
            <?php
                $indent = ((int)($c['level'] ?? 1) - 1) * 24;
                $stateClass = match($c['comment_state']) {
                    'WAITING'  => 'badge--warning',
                    'REJECTED' => 'badge--danger',
                    'REPORTED' => 'badge--danger',
                    default    => 'badge--success',
                };
                $isAuthor = $user && (int)$c['author'] === (int)$user['id'];
            ?>
            <div style="margin-left:<?= $indent ?>px; border-left:3px solid var(--color-border); padding:10px 14px; margin-bottom:8px; border-radius:0 var(--radius) var(--radius) 0;
                        <?= $c['comment_state'] === 'REJECTED' ? 'opacity:.5;' : '' ?>">
                <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:6px; margin-bottom:6px;">
                    <span>
                        <strong><?= htmlspecialchars(($c['firstname'] ?? '?') . ' ' . ($c['lastname'] ?? '')) ?></strong>
                        <span style="color:var(--color-text-muted); font-size:12px; margin-left:6px;"><?= htmlspecialchars(substr($c['created_at'], 0, 16)) ?></span>
                        <?php if ($canMod): ?>
                            <span class="badge <?= $stateClass ?>" style="margin-left:6px; font-size:11px;"><?= htmlspecialchars($c['comment_state']) ?></span>
                        <?php endif; ?>
                    </span>
                    <div style="display:flex; gap:4px; flex-wrap:wrap;">
                        <?php if ($user): ?>
                            <?php if ((int)($c['level'] ?? 1) < 3): ?>
                                <button class="btn btn--ghost" style="font-size:12px; padding:3px 8px;"
                                        onclick="setReply(<?= (int)$c['id'] ?>)">Répondre</button>
                            <?php endif; ?>
                            <?php if (!$isAuthor): ?>
                                <form method="POST" action="<?= BASE_URL ?>index.php?controller=Comments&action=report" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                    <button class="btn btn--ghost" style="font-size:12px; padding:3px 8px;" type="submit">Signaler</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($isAuthor || ($user && $user['role'] === 'admin')): ?>
                                <button class="btn btn--ghost" style="font-size:12px; padding:3px 8px;"
                                        onclick="toggleEdit(<?= (int)$c['id'] ?>)">Modifier</button>
                                <form method="POST" action="<?= BASE_URL ?>index.php?controller=Comments&action=delete"
                                      onsubmit="return confirm('Supprimer ce commentaire ?');" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                    <button class="btn btn--danger" style="font-size:12px; padding:3px 8px;" type="submit">Supprimer</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($canMod): ?>
                                <form method="POST" action="<?= BASE_URL ?>index.php?controller=Comments&action=updateState" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                    <select name="state" onchange="this.form.submit()" style="font-size:12px; padding:3px 6px; border-radius:var(--radius); border:1px solid var(--color-border);">
                                        <option value="">-- statut --</option>
                                        <?php foreach (['APPROVED','WAITING','REJECTED','REPORTED'] as $st): ?>
                                            <option value="<?= $st ?>" <?= $c['comment_state'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <p style="margin:0; white-space:pre-wrap;" id="text-<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['comment_text']) ?></p>
                <!-- Formulaire d'édition inline (caché par défaut) -->
                <?php if ($user && ($isAuthor || $user['role'] === 'admin')): ?>
                    <form method="POST" action="<?= BASE_URL ?>index.php?controller=Comments&action=updateText"
                          id="edit-form-<?= (int)$c['id'] ?>" style="display:none; margin-top:8px;">
                        <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                        <textarea class="form__input" name="comment_text" rows="2" style="margin-bottom:6px;"><?= htmlspecialchars($c['comment_text']) ?></textarea>
                        <button class="btn btn--primary" type="submit" style="font-size:12px; padding:4px 10px;">Enregistrer</button>
                        <button class="btn btn--ghost" type="button" style="font-size:12px; padding:4px 10px;"
                                onclick="toggleEdit(<?= (int)$c['id'] ?>)">Annuler</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
@media (max-width: 640px) {
    .book-show { grid-template-columns: 1fr !important; }
}
.progress-bar {
    background: var(--color-bg-soft);
    border-radius: 99px;
    height: 10px;
    overflow: hidden;
    width: 100%;
}
.progress-bar__fill {
    background: var(--color-primary);
    height: 100%;
    border-radius: 99px;
    transition: width .2s;
}
.star-rating { font-size: 24px; cursor: pointer; color: var(--color-border); }
.star-rating .star { transition: color .1s; }
.star-rating .star.active { color: var(--color-primary); }
.badge--warning { background: #fff3cd; color: #856404; }
.badge--success { background: #d1e7dd; color: #155724; }
.badge--danger  { background: #f8d7da; color: #842029; }
</style>

<script>
// --- Progression : slider live ---
const range = document.getElementById('prog-range');
const fill  = document.getElementById('prog-fill');
const label = document.getElementById('prog-label');
if (range) {
    range.addEventListener('input', () => {
        fill.style.width  = range.value + '%';
        label.textContent = range.value;
    });
}

// --- Étoiles interactives ---
function initStars(containerId, inputId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    const input = document.getElementById(inputId);
    const stars  = container.querySelectorAll('.star');
    stars.forEach(star => {
        star.addEventListener('mouseover', () => {
            stars.forEach(s => s.classList.toggle('active', s.dataset.val <= star.dataset.val));
        });
        star.addEventListener('click', () => {
            input.value = star.dataset.val;
            stars.forEach(s => s.classList.toggle('active', s.dataset.val <= star.dataset.val));
        });
    });
    container.addEventListener('mouseleave', () => {
        stars.forEach(s => s.classList.toggle('active', s.dataset.val <= input.value));
    });
}
initStars('star-rating-input', 'note-input');
initStars('star-rating-edit',  'note-edit');

// --- Réponses ---
function setReply(id) {
    document.getElementById('comment-parent').value = id;
    document.getElementById('reply-id').textContent = id;
    document.getElementById('reply-indicator').style.display = 'inline';
    document.getElementById('cancel-reply').style.display    = 'inline-block';
    document.getElementById('comment-text').focus();
    document.getElementById('comment-form').scrollIntoView({behavior:'smooth'});
}
function cancelReply() {
    document.getElementById('comment-parent').value = '';
    document.getElementById('reply-indicator').style.display = 'none';
    document.getElementById('cancel-reply').style.display    = 'none';
}

// --- Édition inline ---
function toggleEdit(id) {
    const form = document.getElementById('edit-form-' + id);
    const text = document.getElementById('text-' + id);
    const hidden = form.style.display === 'none';
    form.style.display = hidden ? 'block' : 'none';
    text.style.display = hidden ? 'none'  : '';
}
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';
