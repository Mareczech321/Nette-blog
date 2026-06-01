<?php
declare(strict_types=1);

namespace App\Presenters;

use App\Model\CommentFacade;
use App\Model\Mapper\CommentMapper;
use App\Model\ChartManager;
use Nette;
use App\Model\PostFacade;
use App\Model\UserFacade;
use App\Model\FeedManager;
use App\Model\Mapper\PostMapper;

class AdminPresenter extends BasePresenter
{
    public function __construct(
        private PostFacade $postFacade,
        private UserFacade $userFacade,
        private CommentFacade $commentFacade,
        private FeedManager $feedManager,
        private PostMapper $postMapper,
        private CommentMapper $commentMapper,
        private ChartManager $chartManager
    ) {
        parent::__construct();
    }

    public function actionDefault(): void
    {
        if (!$this->getUser()->isLoggedIn() || !$this->getUser()->isInRole('admin')) {
            $this->error('Do administrace mají přístup pouze administrátoři.', Nette\Http\IResponse::S403_FORBIDDEN);
        }
    }

    public function renderDefault(): void
    {
        $postsDto = array_map(fn($r) => $this->postMapper->map($r), iterator_to_array($this->postFacade->getAllPosts()));
        $commentsDto = array_map(fn($r) => $this->commentMapper->map($r), iterator_to_array($this->commentFacade->getAllComments()));

        $totalLikesOnPosts = array_reduce($postsDto, fn($c, $p) => $c + $p->getLikeCount(), 0);
        $totalLikesOnComments = array_reduce($commentsDto, fn($c, $co) => $c + $co->getLikeCount(), 0);

        $chartData = $this->chartManager->generateStatistics($postsDto, $commentsDto);

        $this->template->users = $this->userFacade->getAllUsers();
        $this->template->posts = $postsDto;
        $this->template->comments = $commentsDto;

        $this->template->chart = $chartData;

        $this->template->likesTotal = $totalLikesOnPosts + $totalLikesOnComments;
        $this->template->commentLikes = $totalLikesOnComments;
        $this->template->totalLikesOnComments = $totalLikesOnComments;
        $this->template->totalLikesOnPosts = $totalLikesOnPosts;
    }

    public function handleDeletePost(int $id): void
    {
        $post = $this->postFacade->getPost($id);

        if (!$post) {
            $this->error('Příspěvek neexistuje v DB!');
        }

        $this->postFacade->deletePostAndComments($id);
        $this->flashMessage('Příspěvek byl úspěšně smazán z administrace.', 'info');
        $this->redirect('this');
    }

    public function handleDeleteComment(int $commentId): void
    {
        $comment = $this->postFacade->getComment($commentId);
        if (!$comment) {
            $this->error('Komentář neexistuje v DB!');
        }

        $comment->delete();
        $this->flashMessage('Komentář byl úspěšně smazán z administrace.', 'info');
        $this->redirect('this');
    }

    public function getAllUsers(): Nette\Database\Table\Selection
    {
        return $this->userFacade->getAllUsers();
    }

    public function handleDeleteUser(int $userId): void
    {
        if ($userId === $this->getUser()->getId()) {
            $this->flashMessage('Nemůžete smazat svůj vlastní účet!', 'error');
            $this->redirect('this');
        }

        $userRow = $this->userFacade->getUserByID($userId);
        if ($userRow) {
            $userRow->delete();
            $this->flashMessage('Uživatel byl úspěšně smazán.', 'info');
        }

        $this->redirect('this');
    }

    public function handleChangeRole(int $userId, string $role): void
    {
        if (!$this->getUser()->isInRole('admin')) {
            $this->payload->error = 'Nemáte oprávnění.';
            $this->sendPayload();
        }

        $userRow = $this->userFacade->getUserByID($userId);
        if ($userRow) {
            $userRow->update(['role' => $role]);
        }

        $this->payload->message = 'Role aktualizována';
        $this->sendPayload();
    }

    public function handleUpdateVip(int $userId, string $vipAction): void
    {
        if (!$this->getUser()->isInRole('admin')) {
            $this->payload->error = 'Nemáte oprávnění.';
            $this->sendPayload();
        }

        $this->userFacade->updatePremium($userId, $vipAction);

        if ($this->isAjax()) {
            $userRow = $this->userFacade->getUserByID($userId);

            $formattedDate = 'Bez VIP';
            if ($userRow && $userRow->is_premium && $userRow->premium_duration) {
                $duration = $userRow->premium_duration;

                $dateString = is_string($duration) ? $duration : 'now';
                $dateObj = new \DateTime($dateString);

                $formattedDate = 'do ' . $dateObj->format('j. n. Y');
            }

            $this->payload->success = true;
            $this->payload->newDate = $formattedDate;
            $this->sendPayload();
        } else {
            $this->flashMessage('VIP účet byl úspěšně upraven.', 'success');
            $this->redirect('this');
        }
    }

    public function handleStealArticle(): void
    {
        $adminId = (int) $this->getUser()->getId();

        try {
            $this->feedManager->stealNewestPost($adminId);
            $this->flashMessage('Nejnovější článek byl úspěšně ukraden!', 'success');
        } catch (\Exception $e) {
            $this->flashMessage('Chyba: ' . $e->getMessage(), 'error');
        }

        $this->redirect('this');
    }
}