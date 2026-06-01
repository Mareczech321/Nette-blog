<?php
declare(strict_types=1);

namespace App\Presenters;

use Nette;
use App\Model\PostFacade;
use App\Model\CommentFacade;
use App\Model\UserFacade;
use App\Components\ICommentFormFactory;
use App\Model\DTO\PostLikeDTO;
use App\Components\IEditCommentFormFactory;
use App\Model\PremiumService;
use App\Model\ReactionFasade;
class PostPresenter extends BasePresenter
{
    /** @var ReactionFasade @inject */
    public ReactionFasade $reactionFasade;

    public function __construct(
        private PostFacade              $postFacade,
        private ICommentFormFactory     $commentFormFactory,
        private IEditCommentFormFactory $editFormFactory,
        private CommentFacade           $commentFacade,
        private UserFacade              $userFacade
    ) {
        parent::__construct();
    }

    public function renderDefault(): void
    {
        $this->template->posts = $this->postFacade->getAllPosts();
    }

    public function actionShow(int $id): void
    {
        $post = $this->postFacade->getPost($id);
        if (!$post) {
            $this->error('Stránka nebyla nalezena');
        }
    }

    protected function beforeRender(): void
    {
        parent::beforeRender();
        $this->template->reactionFasade = $this->reactionFasade;
    }

    public function renderShow(int $id, ?int $replyTo = null): void
    {
        $post = $this->postFacade->getPost($id);

        if (!$post) {
            $this->error('Stránka nebyla nalezena');
        }
        $postId = is_scalar($post->id) ? $post->id : null;

        $this->template->post = $post;
        $postUserId = $post->user_id;
        if ($postUserId !== null && is_scalar($postUserId)) {
            $this->template->author = $this->userFacade->getUserByID((int) $postUserId);
        } else {
            $this->template->author = "Neznámý";
        }
        $this->template->comments = $this->postFacade->getCommentsForPost($id)->order('created_at', 'desc');
        $this->template->likeCount = $this->postFacade->getLikeCount($id);
        $this->template->commentFacade = $this->commentFacade;

        $userId = $this->getUser()->isLoggedIn() ? (int) $this->getUser()->getId() : null;
        if ($userId !== null) {
            $likeDto = new PostLikeDTO($id, $userId);
            $this->template->isLiked = $this->postFacade->isLikedBy($likeDto);
        } else {
            $this->template->isLiked = false;
        }

        $comments = $this->postFacade->getCommentsForPost($id)->order('created_at', 'desc');

        $isUserPremium = false;

        if ($this->getUser()->isLoggedIn()) {
            $userDb = $this->userFacade->getUserByID((int) $this->getUser()->getId());

            $isUserPremium = $userDb ? (bool) $userDb->is_premium : false;

            if ($this->getUser()->isInRole('admin')) {
                $isUserPremium = true;
            }
        }

        $this->template->commentCount = is_int($postId) ? count($this->postFacade->getCommentsForPost($postId)) : 0;
        $this->template->replyTo = $replyTo;
        $this->template->topLevelComments = $this->postFacade->getCommentsForPost($id)
            ->where('parent_id', null)
            ->order('created_at', 'ASC');
        $this->template->isLocked = ($post->is_premium && !$isUserPremium);
        $this->template->isUserPremium = $isUserPremium;
        $this->template->comments = $comments;
    }

    protected function createComponentCommentForm(): \App\Components\CommentForm
    {
        $id = $this->getParameter('id');
        $postId = is_scalar($id) ? (int) $id : 0;

        return $this->commentFormFactory->create($postId);
    }

    public function handleDeleteComment(int $commentId): void
    {
        $comment = $this->postFacade->getComment($commentId);

        if (!$comment){
            $this->error('Komentář neexistuje v DB!');
        }

        if ($comment->user_id === $this->getUser()->getId() || $this->getUser()->isInRole('admin')) {
            $comment->delete();
            $this->flashMessage('Komentář byl úspěšně smazán.', 'info');
            $this->redirect('this');
        } else {
            $this->flashMessage('Nemáte oprávnění smazat tento komentář!');
            $this->redirect('Home:');
        }
    }

    public function handleDeletePost(int $id): void{

        $post = $this->postFacade->getPost($id);

        if (!$post){
            $this->error('Příspěvek neexistuje v DB!');
        }

        if ($post->user_id === $this->getUser()->getId() || $this->getUser()->isInRole('admin')) {
            $this->postFacade->deletePostAndComments($id);
            $this->flashMessage('Příspěvek byl úspěšně smazán.', 'info');
            $this->redirect('Home:');
        } else {
            $this->flashMessage('Nemáte oprávnění smazat tento příspěvek!');
            $this->redirect('Home:');
        }
    }

    public function handleLike(int $postId): void
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->flashMessage('Pro lajkování se musíte přihlásit.', 'error');
            $this->redirect('Sign:in');
        }

        $userId = (int)$this->getUser()->getId();

        $likeDto = new PostLikeDTO($postId, $userId);
        $this->postFacade->toggleLike($likeDto);

        if ($this->isAjax()) {
            $this->redrawControl('likeButton');
        } else {
            $this->redirect('this');
        }
    }

    public function handleLikeComment(int $commentId): void
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }

        $this->commentFacade->toggleLike(new \App\Model\DTO\CommentLikeDTO($commentId, (int)$this->getUser()->getId()));

        $this->redrawControl('comment-' . $commentId);
    }

    protected function createComponentEditForm(): \App\Components\EditCommentForm
    {
        return $this->editFormFactory->create();
    }

    public function handleAddEmoji(int $commentId, string $emoji): void
    {
        $this->reactionFasade->addCommentEmojiReaction($commentId, (int)$this->getUser()->getId(), $emoji);

        if ($this->isAjax()) {
            $this->redrawControl('commentsArea');
        } else {
            $this->redirect('this');
        }
    }

    public function handleAddPostEmoji(int $postId, string $emoji): void
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->flashMessage('Pro reakci se přihlaste.', 'error');
            $this->redirect('this');
        }

        $emoji = urldecode($emoji);
        $this->reactionFasade->addPostEmojiReaction($postId, (int)$this->getUser()->getId(), $emoji);

        if ($this->isAjax()) {
            $this->redrawControl('likeButton');
        } else {
            $this->redirect('this');
        }
    }

    public function handleAddCommentEmoji(int $commentId, int $userId, string $emoji): void
    {
        $this->reactionFasade->addCommentEmojiReaction($commentId, (int)$this->getUser()->getId(), $emoji);

        if ($this->isAjax()) {
            $this->redrawControl('commentsArea');
        } else {
            $this->redirect('this');
        }
    }
}