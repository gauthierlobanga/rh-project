<div>
    <!-- Notifications -->
    @if($notifications->count() > 0)
        <div class="fixed top-4 right-4 z-50 max-w-md">
            @foreach($notifications as $notification)
                <div class="mb-2 bg-white rounded-lg shadow-lg border-l-4 border-{{ $notification->couleur }}-500 p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <h4 class="font-semibold text-gray-900">{{ $notification->titre }}</h4>
                            <p class="text-sm text-gray-600 mt-1">{{ $notification->contenu }}</p>
                        </div>
                        <button wire:click="marquerNotificationLue({{ $notification->id }})" 
                                class="text-gray-400 hover:text-gray-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    
    <!-- Reste du code reste identique... -->
    
    <!-- Modal de téléversement -->
    <x-modal wire:model="afficherTeleversement">
        <x-slot name="title">
            Téléverser un document
        </x-slot>
        
        <x-slot name="content">
            <form wire:submit.prevent="televerserDocument">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Fichier
                        </label>
                        <input type="file" wire:model="documentTeleverse" 
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        @error('documentTeleverse') 
                            <span class="text-sm text-red-600">{{ $message }}</span> 
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Type de document
                        </label>
                        <select wire:model="collectionDocument" 
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="pieces_identite">Pièce d'identité</option>
                            <option value="justificatifs_domicile">Justificatif de domicile</option>
                            <option value="documents_bancaires">Document bancaire</option>
                            <option value="autres_documents">Autre document</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Description (optionnel)
                        </label>
                        <textarea wire:model="descriptionDocument" 
                                  rows="3"
                                  class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                  placeholder="Description du document..."></textarea>
                    </div>
                    
                    <div class="text-sm text-gray-500">
                        Formats acceptés: PDF, JPG, PNG (max 5 Mo)
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" wire:click="$set('afficherTeleversement', false)"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Annuler
                    </button>
                    <button type="submit"
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        Téléverser
                    </button>
                </div>
            </form>
        </x-slot>
    </x-modal>
    
    @script
    <script>
        $wire.on('paiement-effectue', function() {
            alert('Paiement effectué avec succès !');
        });
        
        $wire.on('document-televerse', function() {
            alert('Document téléversé avec succès !');
        });
    </script>
    @endscript
</div>