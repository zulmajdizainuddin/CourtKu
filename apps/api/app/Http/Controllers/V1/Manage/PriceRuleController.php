<?php

namespace App\Http\Controllers\V1\Manage;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\PriceRuleRequest;
use App\Http\Resources\PriceRuleResource;
use App\Models\PriceRule;
use App\Models\Venue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * A venue's peak and off-peak prices (FR-10). Owners add, change and remove them; staff can read them.
 * Routes with {priceRule} only find rules of this {venue} (scopeBindings).
 */
class PriceRuleController extends Controller
{
    /** Far more than a real venue needs; stops a script from filling the database. */
    private const MAX_RULES = 50;

    /** GET /api/v1/manage/venues/{venue}/price-rules: all-courts rules first, then by start time. */
    public function index(Venue $venue): AnonymousResourceCollection
    {
        $rules = $venue->priceRules()->orderBy('court_id')->orderBy('starts_at')->orderBy('id')->get();

        return PriceRuleResource::collection($rules);
    }

    /** POST /api/v1/manage/venues/{venue}/price-rules */
    public function store(PriceRuleRequest $request, Venue $venue): JsonResponse
    {
        if ($venue->priceRules()->count() >= self::MAX_RULES) {
            throw new ApiException('PRICE_RULE_LIMIT', 'A venue can have at most '.self::MAX_RULES.' price rules.');
        }

        $rule = $venue->priceRules()->create($request->ruleData());

        return (new PriceRuleResource($rule))->response()->setStatusCode(201);
    }

    /** PUT /api/v1/manage/venues/{venue}/price-rules/{priceRule} */
    public function update(PriceRuleRequest $request, Venue $venue, PriceRule $priceRule): PriceRuleResource
    {
        // TODO(M3): bookings already made keep the price they were quoted.
        $priceRule->update($request->ruleData());

        return new PriceRuleResource($priceRule);
    }

    /** DELETE /api/v1/manage/venues/{venue}/price-rules/{priceRule}: those hours go back to the normal price. */
    public function destroy(Venue $venue, PriceRule $priceRule): Response
    {
        $priceRule->delete();

        return response()->noContent();
    }
}
