<?php

namespace Bluefin\HTML;

class Pagination extends Component
{
    public $rowsPerPage;
    public $totalRows;
    public $currentPage;
    public $totalPages;

    public function __construct(array $attributes = null)
    {
        parent::__construct($attributes);

        $this->addFirstClass('pagination');
    }
}
