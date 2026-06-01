<?php
namespace App\Presenters;

use App\Model\PostFacade;
use Nette;
use Nette\Http\Session;
use App\Model\ElasticSearchFacade;
use App\Components\ISearchFormFactory;
use App\Components\SearchForm;

final class HomePresenter extends BasePresenter
{
    /** @persistent */
    public int $page = 1;
    private Nette\Http\SessionSection $prefsSession;

    public function __construct(
        private PostFacade $postFacade,
        Session $session,
        private ElasticSearchFacade $elasticSearchFacade,
        private ISearchFormFactory $searchFormFactory
    ) {
        parent::__construct();
        $this->prefsSession = $session->getSection('user_preferences');
    }

    public function renderDefault(string $q = ''): void
    {
        $this->template->searchQuery = $q;

        if ($q !== '') {
            $this->template->isSearch = true;

            $this->template->posts = $this->elasticSearchFacade->searchByTitle($q);

            $this->template->paginator = null;
            $this->template->currentOffset = null;

        } else {
            // 2. STANDARDNÍ REŽIM (Zpracuje relační databáze MySQL)
            $this->template->isSearch = false;

            $sessionOffset = $this->prefsSession->get('offset');
            $offset = is_scalar($sessionOffset) ? (int) $sessionOffset : 9;

            $paginator = new \Nette\Utils\Paginator;
            $paginator->setItemsPerPage($offset);
            $paginator->setPage($this->page);
            $paginator->setItemCount($this->postFacade->getPostsCount());

            $this->template->posts = $this->postFacade->getPublicPosts(
                $paginator->getLength(),
                $paginator->getOffset()
            );

            $this->template->paginator = $paginator;
            $this->template->currentOffset = $offset;
        }
    }

    protected function createComponentSearchForm(): SearchForm
    {
        $control = $this->searchFormFactory->create();

        $control->onSearch[] = function (string $query): void {
            $this->redirect('Home:default', ['q' => $query]);
        };

        return $control;
    }

    public function handleChangeOffset(int $offset): void
    {
        $this->prefsSession->set('offset', $offset);

        $this->page = 1;

        if ($this->isAjax()) {
            $this->redrawControl('postsList');
        } else {
            $this->redirect('this');
        }
    }

    public function actionSyncElastic(): void
    {
        $posts = $this->postFacade->getPublicPosts(1000, 0);

        $count = 0;
        foreach ($posts as $post) {
            $content = $post->content;
            $id = $post->id;
            $title = $post->title;
            $image = $post->image;

            assert(is_string($content) || is_null($content));
            assert(is_int($id) || is_string($id));
            assert(is_string($title) || is_null($title));
            assert(is_string($image) || is_null($image));

            $cleanContent = strip_tags((string) $content);

            $this->elasticSearchFacade->indexArticle(
                (int) $id,
                (string) $title,
                $cleanContent,
                $image ? (string) $image : null
            );
            $count++;
        }

        $this->flashMessage("Úspěšně synchronizováno $count článků do Elasticsearch!", 'success');
        $this->redirect('Home:default');
    }
}