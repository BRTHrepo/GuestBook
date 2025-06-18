<?php
require_once BASE_PATH . '/src/views/layout/header.php';
require_once BASE_PATH . '/src/helpers/LanguageHelper.php';
use helpers\LanguageHelper;

?>


    <div class="container my-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <?php if ($result['success']): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <h4 class="alert-heading"><?= LanguageHelper::get('success') ?>!</h4>
                                <p><?= htmlspecialchars($result['message']) ?></p>

                            </div>
                            <div class="text-center mt-3">
                                <a href="/guestbook" class="btn btn-primary">
                                    <?= LanguageHelper::get('back_to_guestbook') ?>
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <h4 class="alert-heading"><?= LanguageHelper::get('error') ?>!</h4>
                                <p><?= htmlspecialchars($result['message']) ?></p>
                                <hr>
                                <p class="mb-0"><?= htmlspecialchars($result['fault']) ?></p>

                            </div>
                            <div class="text-center mt-3">
                                <a >  <?= LanguageHelper::get('contact_support') ?>                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php require_once BASE_PATH . '/src/views/layout/footer.php'; ?>