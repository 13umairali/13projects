<?php

namespace Modules\SMS\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Modules\SMS\Http\Requests\PhoneBookRequest;
use Modules\SMS\Services\PhoneBookCategoryService;
use Modules\SMS\Services\PhoneBookService;
use RealRashid\SweetAlert\Facades\Alert;

class PhoneBookController extends Controller
{
    public function __construct(
        private readonly PhoneBookService $phoneBookService,
        private readonly PhoneBookCategoryService $phoneBookCategoryService
    ) {}

    public function index(): Factory|View
    {
        return view('sms::phone-book.index', [
            'phoneBooks' => $this->phoneBookService->getPhoneBooks(),
            'phoneBookCategories' => $this->phoneBookCategoryService->getPhoneBookCategories(),
        ]);
    }

    public function store(PhoneBookRequest $request): Redirector|RedirectResponse
    {
        $this->phoneBookService->store($request->validated());

        Alert::toast('Phone Book has been create successfully.', 'success');

        return redirect('phone-book');
    }

    public function show(int $id): Factory|Redirector|RedirectResponse|View
    {
        $phoneBook = $this->phoneBookService->findById($id);
        if (! $phoneBook) {
            Alert::error('Error', _lang('Something went wrong. Phone book not found.'));

            return redirect('phone-book');
        }

        return view('sms::phone-book.show', compact('phoneBook'));
    }

    public function edit(int $id): Factory|Redirector|RedirectResponse|View
    {
        $phoneBook = $this->phoneBookService->findById($id);
        $phoneBookCategories = $this->phoneBookCategoryService->getPhoneBookCategories();
        if (! $phoneBook) {
            Alert::error('Error', _lang('Something went wrong. Phone book not found.'));

            return redirect('phone-book');
        }

        return view(
            'sms::phone-book.edit',
            [
                'phoneBook' => $phoneBook,
                'phoneBookCategories' => $phoneBookCategories,
            ]
        );
    }

    public function update(PhoneBookRequest $request, int $id): Redirector|RedirectResponse
    {
        $phoneBook = $this->phoneBookService->findById($id);
        if (! $phoneBook) {
            Alert::error('Error', _lang('Something went wrong. Phone book not found.'));

            return redirect('phone-book');
        }
        $this->phoneBookService->update($request->validated(), $id);

        Alert::toast('Phone Book has been update successfully.', 'success');

        return redirect('phone-book');
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $phoneBook = $this->phoneBookService->findById($id);
        if (! $phoneBook) {
            Alert::error('Error', _lang('Something went wrong. Phone book not found.'));

            return redirect('phone-book');
        }
        $this->phoneBookService->delete($id);

        Alert::toast('Phone Book has been delete successfully.', 'success');

        return redirect('phone-book');
    }
}
