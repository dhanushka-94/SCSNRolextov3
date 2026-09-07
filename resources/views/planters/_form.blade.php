@php
    $isEdit = $planter->exists;
@endphp

<div class="space-y-8" data-certification-form>
    <section class="space-y-5">
        <h2 class="border-b border-sand pb-2 text-base font-semibold text-forest-dark">Applicant details</h2>

        <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="name" class="label-field">Applicant name</label>
                <input id="name" name="name" type="text" value="{{ old('name', $planter->name) }}" required class="input-field">
                @error('name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="nic" class="label-field">NIC number</label>
                <input id="nic" name="nic" type="text" value="{{ old('nic', $planter->nic) }}" required class="input-field">
                @error('nic')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="phone" class="label-field">Phone</label>
                <input id="phone" name="phone" type="text" value="{{ old('phone', $planter->phone) }}" required class="input-field">
                @error('phone')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="email" class="label-field">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $planter->email) }}" required class="input-field">
                @error('email')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <x-district-rdo-fields
                :district-id="$planter->district_id"
                :rdo-division-id="$planter->rdo_division_id"
            />
        </div>
    </section>

    <section class="space-y-5">
        <h2 class="border-b border-sand pb-2 text-base font-semibold text-forest-dark">General farm information</h2>

        <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="farm_name" class="label-field">Farm name</label>
                <input id="farm_name" name="farm_name" type="text" value="{{ old('farm_name', $planter->farm_name) }}" class="input-field">
                @error('farm_name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="address" class="label-field">Farm address</label>
                <textarea id="address" name="address" rows="3" required class="input-field">{{ old('address', $planter->address) }}</textarea>
                @error('address')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <x-map-pin-fields
                    :latitude="$planter->latitude"
                    :longitude="$planter->longitude"
                />
            </div>
            <div>
                <label for="whatsapp" class="label-field">WhatsApp number</label>
                <input id="whatsapp" name="whatsapp" type="text" value="{{ old('whatsapp', $planter->whatsapp) }}" class="input-field">
                @error('whatsapp')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="fax" class="label-field">Fax number</label>
                <input id="fax" name="fax" type="text" value="{{ old('fax', $planter->fax) }}" class="input-field">
                @error('fax')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <p class="label-field">Nature of business</p>
            <div class="mt-2 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                @foreach (\App\Models\Planter::businessTypes() as $value => $label)
                    <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-line bg-cream/60 px-3 py-2.5 text-sm">
                        <input
                            type="radio"
                            name="business_type"
                            value="{{ $value }}"
                            @checked(old('business_type', $planter->business_type) === $value)
                        >
                        <span class="font-medium text-bark-dark">{{ $label['en'] }}</span>
                    </label>
                @endforeach
            </div>
            @error('business_type')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </div>
    </section>

    <section class="space-y-5">
        <h2 class="border-b border-sand pb-2 text-base font-semibold text-forest-dark">Certification history</h2>

        @php $alreadyCertified = old('already_certified', $planter->already_certified); @endphp
        <div>
            <p class="label-field">Is the farm already certified?</p>
            <div class="mt-2 flex flex-wrap gap-4">
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="radio" name="already_certified" value="1" data-toggle-group="already-certified" @checked((string) $alreadyCertified === '1' || $alreadyCertified === true)>
                    Yes
                </label>
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="radio" name="already_certified" value="0" data-toggle-group="already-certified" @checked((string) $alreadyCertified === '0' || $alreadyCertified === false)>
                    No
                </label>
            </div>
            @error('already_certified')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </div>

        <div class="{{ ((string) $alreadyCertified === '1' || $alreadyCertified === true) ? '' : 'hidden' }}" data-panel="already-certified" data-show-when="1">
            <label for="certification_standard" class="label-field">Under which standard?</label>
            <input id="certification_standard" name="certification_standard" type="text" value="{{ old('certification_standard', $planter->certification_standard) }}" class="input-field">
            @error('certification_standard')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </div>

        @php $certIssue = old('certification_rejected_or_suspended', $planter->certification_rejected_or_suspended); @endphp
        <div>
            <p class="label-field">Previously rejected or suspended?</p>
            <div class="mt-2 flex flex-wrap gap-4">
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="radio" name="certification_rejected_or_suspended" value="1" data-toggle-group="cert-issue" @checked((string) $certIssue === '1' || $certIssue === true)>
                    Yes
                </label>
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="radio" name="certification_rejected_or_suspended" value="0" data-toggle-group="cert-issue" @checked((string) $certIssue === '0' || $certIssue === false)>
                    No
                </label>
            </div>
            @error('certification_rejected_or_suspended')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </div>

        <div class="{{ ((string) $certIssue === '1' || $certIssue === true) ? '' : 'hidden' }}" data-panel="cert-issue" data-show-when="1">
            <label for="certification_issue_reason" class="label-field">Reason</label>
            <textarea id="certification_issue_reason" name="certification_issue_reason" rows="3" class="input-field">{{ old('certification_issue_reason', $planter->certification_issue_reason) }}</textarea>
            @error('certification_issue_reason')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </div>
    </section>

    <section class="space-y-5">
        <h2 class="border-b border-sand pb-2 text-base font-semibold text-forest-dark">Products for certification</h2>

        <x-crop-repeater
            :values="old('crops_products', $planter->crops_products ?? [])"
            placeholder="Product"
            add-label="Add field"
            max-hint="Maximum 10 items"
        />
        @error('crops_products')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        @error('crops_products.*')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </section>

    <section class="space-y-5">
        <h2 class="border-b border-sand pb-2 text-base font-semibold text-forest-dark">Awareness and processing</h2>

        @php
            $aware = old('aware_of_certification', $planter->aware_of_certification);
            $leaflet = old('has_certification_leaflet', $planter->has_certification_leaflet);
            $processing = old('processes_rubber_on_farm', $planter->processes_rubber_on_farm);
        @endphp

        <div>
            <p class="label-field">Aware of currently used certification?</p>
            <div class="mt-2 flex flex-wrap gap-4">
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="radio" name="aware_of_certification" value="1" @checked((string) $aware === '1' || $aware === true)>
                    Yes
                </label>
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="radio" name="aware_of_certification" value="0" @checked((string) $aware === '0' || $aware === false)>
                    No
                </label>
            </div>
            @error('aware_of_certification')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </div>

        <div>
            <p class="label-field">Have an information leaflet?</p>
            <div class="mt-2 flex flex-wrap gap-4">
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="radio" name="has_certification_leaflet" value="1" @checked((string) $leaflet === '1' || $leaflet === true)>
                    Yes
                </label>
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="radio" name="has_certification_leaflet" value="0" @checked((string) $leaflet === '0' || $leaflet === false)>
                    No
                </label>
            </div>
            @error('has_certification_leaflet')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </div>

        <div>
            <p class="label-field">Natural rubber processed on farm?</p>
            <div class="mt-2 flex flex-wrap gap-4">
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="radio" name="processes_rubber_on_farm" value="1" data-toggle-group="processing" @checked((string) $processing === '1' || $processing === true)>
                    Yes
                </label>
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="radio" name="processes_rubber_on_farm" value="0" data-toggle-group="processing" @checked((string) $processing === '0' || $processing === false)>
                    No
                </label>
            </div>
            @error('processes_rubber_on_farm')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </div>

        <div class="{{ ((string) $processing === '1' || $processing === true) ? '' : 'hidden' }}" data-panel="processing" data-show-when="1">
            <p class="label-field">Is there a process plan?</p>
            <div class="mt-2 flex flex-wrap gap-4">
                @foreach (\App\Models\Planter::processPlanOptions() as $value => $label)
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="radio" name="has_process_plan" value="{{ $value }}" @checked(old('has_process_plan', $planter->has_process_plan) === $value)>
                        {{ $label['en'] }}
                    </label>
                @endforeach
            </div>
            @error('has_process_plan')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </div>
    </section>

    <section class="space-y-5">
        <h2 class="border-b border-sand pb-2 text-base font-semibold text-forest-dark">Plantation company or group</h2>
        <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="group_name" class="label-field">Name</label>
                <input id="group_name" name="group_name" type="text" value="{{ old('group_name', $planter->group_name) }}" class="input-field">
                @error('group_name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="group_address" class="label-field">Address</label>
                <textarea id="group_address" name="group_address" rows="2" class="input-field">{{ old('group_address', $planter->group_address) }}</textarea>
                @error('group_address')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section class="space-y-5">
        <h2 class="border-b border-sand pb-2 text-base font-semibold text-forest-dark">Admin status</h2>
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="status" class="label-field">Approval status</label>
                <select id="status" name="status" class="input-field">
                    @foreach (\App\Models\Planter::statuses() as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $planter->status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="rejection_reason" class="label-field">Rejection reason</label>
                <input id="rejection_reason" name="rejection_reason" type="text" value="{{ old('rejection_reason', $planter->rejection_reason) }}" class="input-field" placeholder="Required if rejected">
                @error('rejection_reason')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password" class="label-field">{{ $isEdit ? 'New password' : 'Password' }}</label>
                <div class="relative">
                    <input id="password" name="password" type="password" autocomplete="new-password" class="input-field pr-12">
                    <button type="button" data-password-toggle="password" class="absolute inset-y-0 right-0 px-3 text-xs font-semibold text-bark">Show</button>
                </div>
                <p class="mt-1 text-xs text-muted">{{ $isEdit ? 'Leave blank to keep the current password.' : 'Optional. The applicant can create a password after approval.' }}</p>
                @error('password')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password_confirmation" class="label-field">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="input-field">
            </div>
        </div>
    </section>
</div>
