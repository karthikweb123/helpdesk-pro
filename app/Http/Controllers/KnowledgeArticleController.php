<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeArticle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KnowledgeArticleController extends Controller
{
    private const CATEGORIES = ['Hardware', 'Software', 'Network', 'Account & access', 'Other'];

    public function index(Request $request): View
    {
        $query = KnowledgeArticle::query()->with('author')->latest();
        if ($request->filled('q')) {
            $search = trim($request->string('q')->toString());
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            });
        }

        return view('knowledge.index', ['articles' => $query->paginate(12)->withQueryString()]);
    }

    public function show(KnowledgeArticle $article): View
    {
        return view('knowledge.show', compact('article'));
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->isStaff(), 403);

        return view('knowledge.form', ['article' => new KnowledgeArticle, 'mode' => 'Create']);
    }

    public function store(Request $request): RedirectResponse
    {
        $article = $request->user()->isStaff()
            ? KnowledgeArticle::create($this->validated($request))
            : abort(403);

        return redirect()->route('knowledge.show', $article)->with('status', 'Knowledge article published.');
    }

    public function edit(Request $request, KnowledgeArticle $article): View
    {
        abort_unless($request->user()->isStaff(), 403);

        return view('knowledge.form', ['article' => $article, 'mode' => 'Edit']);
    }

    public function update(Request $request, KnowledgeArticle $article): RedirectResponse
    {
        abort_unless($request->user()->isStaff(), 403);
        $article->update($this->validated($request));

        return redirect()->route('knowledge.show', $article)->with('status', 'Knowledge article updated.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'category' => ['required', Rule::in(self::CATEGORIES)],
            'summary' => ['required', 'string', 'max:500'],
            'body' => ['required', 'string', 'max:30000'],
        ]);
    }
}
