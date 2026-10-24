<?php

declare(strict_types=1);

namespace Modules\SMS\Services;

use Modules\SMS\Models\PhoneBook;
use Modules\SMS\Repositories\PhoneBookRepository;

class PhoneBookService
{
    public function __construct(
        private readonly PhoneBookRepository $phoneBookRepository
    ) {}

    public function getPhoneBooks()
    {
        return $this->phoneBookRepository->all();
    }

    public function store(array $data): ?PhoneBook
    {
        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();

        return $this->phoneBookRepository->create($data);
    }

    public function update(array $data, $id): ?bool
    {
        return $this->phoneBookRepository->update($data, $id);
    }

    public function findById($id): ?PhoneBook
    {
        return $this->phoneBookRepository->show((int) $id);
    }

    public function delete($id): ?bool
    {
        return $this->phoneBookRepository->delete($id);
    }
}
