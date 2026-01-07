<?php

namespace App\Http\Controllers;

use App\Models\WeddingPage;
use App\Services\ContentBuilderService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class WeddingPageController extends Controller
{
    public function __construct(
        private ContentBuilderService $contentBuilder
    ) {}

    public function index(Request $request)
    {
        $pages = $request->user()->weddingPages()->orderBy('created_at', 'desc')->get();
        return response()->json($pages);
    }

    public function store(Request $request)
    {
        $request->validate([
            'coupleName1' => 'required|string',
            'coupleName2' => 'required|string',
            'eventLocation' => 'required|string',
            'eventDate' => 'required|date',
            'receptionTime' => 'required|string',
            'ceremonyTime' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
        ]);

        $funnelData = $request->all();

        // Build content JSON from funnel data
        $contentJson = $this->contentBuilder->buildFromFunnelData($funnelData);

        // Set availability dates (1 month before and after event)
        $eventDate = Carbon::parse($funnelData['eventDate']);
        $availableFrom = $eventDate->copy()->subMonth();
        $availableUntil = $eventDate->copy()->addMonth();

        $page = WeddingPage::create([
            'user_id' => $request->user()->id,
            'slug' => Str::slug(Str::random(12)),
            'content_json' => $contentJson,
            'design_settings' => [
                'fontSize' => 'medium',
                'primaryColor' => '#ec4899',
                'backgroundColor' => '#ffffff',
                'fontFamily' => 'Heebo',
            ],
            'ai_settings' => [
                'style' => $funnelData['aiStyle'] ?? 'classic-painting',
                'couplePhoto' => $funnelData['couplePhoto'] ?? null,
                'generatedImage' => $funnelData['generatedImage'] ?? null,
            ],
            'status' => 'preview',
            'price' => 300.00,
            'available_from' => $availableFrom,
            'available_until' => $availableUntil,
        ]);

        // TODO: Send preview ready email

        return response()->json($page, 201);
    }

    public function show(Request $request, string $slug)
    {
        $page = WeddingPage::where('slug', $slug)->firstOrFail();

        // Check if user owns the page or if page is live
        if ($page->user_id !== $request->user()?->id && !$page->isLive()) {
            return response()->json(['message' => 'Page not found'], 404);
        }

        return response()->json($page);
    }

    public function update(Request $request, int $id)
    {
        $page = WeddingPage::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $page->update($request->only([
            'content_json',
            'design_settings',
            'ai_settings',
        ]));

        return response()->json($page);
    }

    public function updateDesign(Request $request, int $id)
    {
        $page = WeddingPage::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $request->validate([
            'fontSize' => 'in:small,medium,large',
            'primaryColor' => 'string',
            'backgroundColor' => 'string',
            'fontFamily' => 'in:Playfair,Heebo,Assistant,David Libre',
        ]);

        $page->update([
            'design_settings' => array_merge($page->design_settings, $request->all()),
        ]);

        return response()->json($page);
    }

    public function requestRevision(Request $request, int $id)
    {
        $page = WeddingPage::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $request->validate([
            'message' => 'required|string',
        ]);

        $revision = $page->revisionRequests()->create([
            'message' => $request->message,
        ]);

        // TODO: Send revision request email

        return response()->json($revision, 201);
    }
}

