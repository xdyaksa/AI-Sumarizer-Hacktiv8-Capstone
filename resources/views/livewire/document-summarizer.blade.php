<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">AI Document Summarizer</div>
                <div class="card-body">
                                    @php
                                        $isDocument = !empty($document);
                                        $isLink = !empty($link);
                                        $isText = !empty($text);
                                        $disableOthers = $isDocument || $isLink || $isText;
                                    @endphp
                                    <form wire:submit.prevent="summarize">
                                        <div class="mb-3">
                                            <label for="document" class="form-label">Upload Dokumen (PDF/DOCX/TXT)</label>
                                            <input type="file" id="document" class="form-control" wire:model="document" accept=".pdf,.doc,.docx,.txt"
                                                @if($disableOthers && !$isDocument) disabled @endif
                                                wire:change="$set('link', null); $set('text', null)">
                                        </div>
                                        <div class="mb-3">
                                            <label for="link" class="form-label">Atau Masukkan Link Dokumen</label>
                                            <input type="url" id="link" class="form-control" wire:model="link" placeholder="https://..."
                                                @if($disableOthers && !$isLink) disabled @endif
                                                wire:input="$set('document', null); $set('text', null)">
                                        </div>
                                        <div class="mb-3">
                                            <label for="text" class="form-label">Atau Masukkan Teks</label>
                                            <textarea id="text" class="form-control" wire:model="text" rows="5" placeholder="Tulis atau tempel teks di sini..."
                                                @if($disableOthers && !$isText) disabled @endif
                                                wire:input="$set('document', null); $set('link', null)"></textarea>
                                        </div>
                                        @if($disableOthers)
                                            <div class="alert alert-info">Hanya satu input yang dapat diisi, input lain otomatis nonaktif.</div>
                                        @endif
                                        <button type="submit" class="btn btn-success">Ringkas</button>
                                        <button type="button" class="btn btn-secondary ms-2" wire:click="clearInput">Clear Input</button>
                                    </form>
                    <hr>
                    @if($summary)
                            <div class="alert alert-info mt-3">
                                <strong>Hasil Ringkasan:</strong>
                                <p>{!! $summary !!}</p>
                                @if(str_ends_with($summary, '...') && !$showFull)
                                    <button wire:click="showMore" class="btn btn-link p-0">Lihat lebih banyak</button>
                                @endif
                            </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
