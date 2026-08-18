<?php

use App\Http\Controllers\Admin\AccessRequestController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DocumentController as AdminDocumentController;
use App\Http\Controllers\Admin\DocumentFolderController;
use App\Http\Controllers\Admin\ExpiryNotificationController;
use App\Http\Controllers\Admin\InstitutionFolderController;
use App\Http\Controllers\Admin\MonitoringController;
use App\Http\Controllers\Admin\PartnershipApplicationController as AdminPartnershipApplicationController;
use App\Http\Controllers\Admin\RekapitulasiController;
use App\Http\Controllers\Admin\SatisfactionQuestionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\YearFolderController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\CooperationController;
use App\Http\Controllers\DocumentFileController;
use App\Http\Controllers\DocumentPreviewController;
use App\Http\Controllers\DocumentRequestController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PartnershipApplicationController;
use App\Http\Controllers\PartnershipApplicationTemplateController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SatisfactionSurveyController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/profil', [PageController::class, 'profil'])->name('pages.profil');
Route::get('/mitra', [PageController::class, 'mitra'])->name('pages.mitra');
Route::get('/skema-kerjasama', [PageController::class, 'skema'])->name('pages.skema');
Route::get('/kontak', [PageController::class, 'kontak'])->name('pages.kontak');

Route::get('/ajukan-kerjasama', [PartnershipApplicationController::class, 'create'])
    ->name('partnership-applications.create');
Route::get('/ajukan-kerjasama/template', PartnershipApplicationTemplateController::class)
    ->name('partnership-applications.template');
Route::post('/ajukan-kerjasama', [PartnershipApplicationController::class, 'store'])
    ->middleware('throttle:'.config('partnership_applications.rate_limit_per_hour', 5).',60')
    ->name('partnership-applications.store');
Route::get('/ajukan-kerjasama/berhasil/{application:public_token}', [PartnershipApplicationController::class, 'success'])
    ->name('partnership-applications.success');

Route::get('/evaluasi/{survey:public_token}', [SatisfactionSurveyController::class, 'show'])
    ->name('satisfaction.show');
Route::post('/evaluasi/{survey:public_token}', [SatisfactionSurveyController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('satisfaction.store');
Route::get('/evaluasi/{survey:public_token}/terima-kasih', [SatisfactionSurveyController::class, 'thanks'])
    ->name('satisfaction.thanks');

Route::prefix('kerjasama')->name('cooperation.')->group(function () {
    Route::get('{section:slug}', [CooperationController::class, 'section'])->name('section');
    Route::get('{section:slug}/{category:slug}', [CooperationController::class, 'category'])->name('category');
    Route::get('{section:slug}/{category:slug}/{year}', [CooperationController::class, 'year'])->name('year');
    Route::get('{section:slug}/{category:slug}/{year}/folder/{folder:uuid}', [CooperationController::class, 'folder'])
        ->name('folder');
    Route::get('{section:slug}/{category:slug}/{year}/{institution:slug}', [CooperationController::class, 'institution'])
        ->name('institution');
});

Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

// Public thumbnail only — never serves private originals.
Route::get('/previews/{document}', [DocumentPreviewController::class, 'show'])
    ->name('documents.preview');

Route::middleware('auth')->group(function () {
    Route::get('/documents/{document}/request', [DocumentRequestController::class, 'show'])
        ->name('documents.request');
    Route::post('/documents/{document}/request-access', [DocumentRequestController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('documents.request-access');
    Route::get('/documents/{document}/view', [DocumentFileController::class, 'view'])
        ->name('documents.view');
    Route::get('/documents/{document}/download', [DocumentFileController::class, 'download'])
        ->name('documents.download');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user?->canAccessAdminPanel()) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('home');
})->middleware(['auth', 'active'])->name('dashboard');

Route::prefix('admin')
    ->middleware(['auth', 'active', 'staff'])
    ->name('admin.')
    ->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::get('rekapitulasi', [RekapitulasiController::class, 'index'])->name('rekapitulasi.index');
        Route::get('notifikasi-masa-berlaku', [ExpiryNotificationController::class, 'index'])->name('expiry.index');
        Route::get('monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
        Route::post('monitoring/{document}/catatan', [MonitoringController::class, 'storeNote'])->name('monitoring.notes.store');
        Route::post('monitoring/survei', [MonitoringController::class, 'storeSurvey'])->name('monitoring.surveys.store');
        Route::get('evaluasi-kepuasan/indikator', [SatisfactionQuestionController::class, 'index'])->name('satisfaction.questions.index');
        Route::post('evaluasi-kepuasan/indikator', [SatisfactionQuestionController::class, 'store'])->name('satisfaction.questions.store');
        Route::put('evaluasi-kepuasan/indikator/{question}', [SatisfactionQuestionController::class, 'update'])->name('satisfaction.questions.update');

        Route::get('years', [YearFolderController::class, 'index'])->name('years.index');
        Route::get('years/create', [YearFolderController::class, 'create'])->name('years.create');
        Route::post('years', [YearFolderController::class, 'store'])->name('years.store');
        Route::get('years/{yearFolder}/edit', [YearFolderController::class, 'edit'])->name('years.edit');
        Route::put('years/{yearFolder}', [YearFolderController::class, 'update'])->name('years.update');
        Route::delete('years/{yearFolder}', [YearFolderController::class, 'destroy'])->name('years.destroy');

        Route::get('years/{yearFolder:id}/document-folders', [DocumentFolderController::class, 'optionsForYear'])
            ->name('years.document-folders');

        Route::get('folders', [DocumentFolderController::class, 'index'])->name('folders.index');
        Route::post('folders', [DocumentFolderController::class, 'store'])->name('folders.store');
        Route::put('folders/{folder:uuid}', [DocumentFolderController::class, 'update'])->name('folders.update');
        Route::post('folders/{folder:uuid}/move', [DocumentFolderController::class, 'move'])->name('folders.move');
        Route::post('folders/{folder:uuid}/reorder', [DocumentFolderController::class, 'reorder'])->name('folders.reorder');
        Route::delete('folders/{folder:uuid}', [DocumentFolderController::class, 'destroy'])->name('folders.destroy');

        Route::get('institutions', [InstitutionFolderController::class, 'index'])->name('institutions.index');
        Route::get('institutions/create', [InstitutionFolderController::class, 'create'])->name('institutions.create');
        Route::post('institutions', [InstitutionFolderController::class, 'store'])->name('institutions.store');
        Route::get('institutions/{institution}/edit', [InstitutionFolderController::class, 'edit'])->name('institutions.edit');
        Route::put('institutions/{institution}', [InstitutionFolderController::class, 'update'])->name('institutions.update');
        Route::delete('institutions/{institution}', [InstitutionFolderController::class, 'destroy'])->name('institutions.destroy');

        Route::get('documents', [AdminDocumentController::class, 'index'])->name('documents.index');
        Route::get('documents/create', [AdminDocumentController::class, 'create'])->name('documents.create');
        Route::post('documents', [AdminDocumentController::class, 'store'])->name('documents.store');
        Route::get('documents/{document}/edit', [AdminDocumentController::class, 'edit'])->name('documents.edit');
        Route::put('documents/{document}', [AdminDocumentController::class, 'update'])->name('documents.update');
        Route::post('documents/{document}/move', [AdminDocumentController::class, 'move'])->name('documents.move');
        Route::delete('documents/{document}', [AdminDocumentController::class, 'destroy'])->name('documents.destroy');

        Route::get('access-requests', [AccessRequestController::class, 'index'])->name('access-requests.index');
        Route::get('access-requests/{accessRequest}', [AccessRequestController::class, 'show'])->name('access-requests.show');
        Route::post('access-requests/{accessRequest}/approve', [AccessRequestController::class, 'approve'])->name('access-requests.approve');
        Route::post('access-requests/{accessRequest}/reject', [AccessRequestController::class, 'reject'])->name('access-requests.reject');
        Route::post('access-requests/{accessRequest}/revoke', [AccessRequestController::class, 'revoke'])->name('access-requests.revoke');

        Route::get('partnership-applications', [AdminPartnershipApplicationController::class, 'index'])
            ->name('partnership-applications.index');
        Route::get('partnership-applications/{application}', [AdminPartnershipApplicationController::class, 'show'])
            ->name('partnership-applications.show');
        Route::patch('partnership-applications/{application}/status', [AdminPartnershipApplicationController::class, 'updateStatus'])
            ->name('partnership-applications.status');
        Route::get(
            'partnership-applications/{application}/attachments/{attachment}/download',
            [AdminPartnershipApplicationController::class, 'downloadAttachment']
        )->name('partnership-applications.attachments.download');

        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });

require __DIR__.'/auth.php';
