<?php
require_once BASE_PATH . '/src/views/layout/header.php';
require_once BASE_PATH . '/src/helpers/LanguageHelper.php';
use controllers\MessageController;
use helpers\LanguageHelper;

?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h2 class="text-center mb-4"><?= LanguageHelper::get('rankings_title') ?></h2>

            <div class="rankings-table-container">
                <table class="table table-hover var rankings-table">
                    <thead class="rankings-header">
                    <tr>
                        <th style="text-align: center;"><?= LanguageHelper::get('best_score') ?></th>
                        <th style="text-align: center;"><?= LanguageHelper::get('total_score') ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>

                        <td><a href="?action=ranking&type=daily&mode=best" class="btn btn-outline-primary btn-sm rankings-btn"><?= LanguageHelper::get('daily') ?></a></td>
                        <td><a href="?action=ranking&type=daily&mode=total" class="btn btn-outline-primary btn-sm rankings-btn"><?= LanguageHelper::get('daily')?></a></td>
                    </tr>
                    <tr>
                        <td><a href="?action=ranking&type=monthly&mode=best" class="btn btn-outline-primary btn-sm rankings-btn"><?= LanguageHelper::get('monthly')  ?></a></td>
                        <td><a href="?action=ranking&type=monthly&mode=total" class="btn btn-outline-primary btn-sm rankings-btn"><?= LanguageHelper::get('monthly')  ?></a></td>
                    </tr>
                    <tr>
                        <td><a href="?action=ranking&type=yearly&mode=best" class="btn btn-outline-primary btn-sm rankings-btn"><?=  LanguageHelper::get('yearly') ?></a></td>
                        <td><a href="?action=ranking&type=yearly&mode=total" class="btn btn-outline-primary btn-sm rankings-btn"><?=  LanguageHelper::get('yearly') ?></a></td>
                    </tr>
                    <tr>
                        <td><a href="?action=ranking&type=all_time&mode=best" class="btn btn-outline-primary btn-sm rankings-btn"><?=  LanguageHelper::get('all_time') ?></a></td>
                        <td><a href="?action=ranking&type=all_time&mode=total" class="btn btn-outline-primary btn-sm rankings-btn"><?=  LanguageHelper::get('all_time') ?></a></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . '/src/views/layout/footer.php'; ?>
