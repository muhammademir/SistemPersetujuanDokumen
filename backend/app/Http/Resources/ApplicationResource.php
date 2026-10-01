<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'code'           => $this->code,
            'title'          => $this->title,
            'description'    => $this->description,
            'document_type'  => $this->document_type,
            'status'         => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
            ],
            'revision_count' => $this->revision_count,
            'submitted_at'   => $this->submitted_at?->toIso8601String(),
            'decided_at'     => $this->decided_at?->toIso8601String(),
            'created_at'     => $this->created_at?->toIso8601String(),
            'updated_at'     => $this->updated_at?->toIso8601String(),

            'applicant' => $this->whenLoaded('applicant', fn ($u) => [
                'id'           => $u->id,
                'name'         => $u->name,
                'company_name' => $u->company_name,
            ]),
            'reviewer' => $this->whenLoaded('reviewer', fn ($u) => [
                'id'   => $u->id,
                'name' => $u->name,
            ]),

            'documents_count' => $this->whenCounted('documents'),

            'documents' => $this->whenLoaded('documents', fn ($docs) => $docs->map(fn ($d) => [
                'id'              => $d->id,
                'original_name'   => $d->original_name,
                'mime_type'       => $d->mime_type,
                'size_bytes'      => $d->size_bytes,
                'revision_number' => $d->revision_number,
                'uploaded_at'     => $d->created_at?->toIso8601String(),
            ])),

            'reviews' => $this->whenLoaded('reviews', fn ($reviews) => $reviews->map(fn ($r) => [
                'id'              => $r->id,
                'decision'        => $r->decision,
                'note'            => $r->note,
                'revision_number' => $r->revision_number,
                'reviewed_at'     => $r->reviewed_at?->toIso8601String(),
                'reviewer'        => $r->relationLoaded('reviewer') ? $r->reviewer?->name : null,
            ])),

            'status_logs' => $this->whenLoaded('statusLogs', fn ($logs) => $logs->map(fn ($l) => [
                'id'          => $l->id,
                'from_status' => $l->from_status,
                'to_status'   => $l->to_status,
                'note'        => $l->note,
                'actor'       => $l->relationLoaded('actor') ? $l->actor?->name : null,
                'created_at'  => $l->created_at?->toIso8601String(),
            ])),
        ];
    }
}
