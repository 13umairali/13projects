<div class="row">
    <div class="col-12 col-lg-6 mt-20">
        {{-- General Information --}}
        <div class="p-16 rounded-16 border-gray-200">
            <h3 class="font-14 mb-24">{{ trans('update.general_information') }}</h3>

            @include('design_1.panel.includes.locale.locale_select',[
                'itemRow' => !empty($landingComponent) ? $landingComponent : null,
                'withoutReloadLocale' => false,
                'extraClass' => ''
            ])

            <x-landingBuilder-switch
                label="{{ trans('update.enable_component') }}"
                id="enable"
                name="enable"
                checked="{{ !!($landingComponent->enable) }}"
                hint=""
                className="mb-0"
            />
        </div>

        {{-- Main Content --}}
        <div class="p-16 rounded-16 border-gray-200 mt-20">
            <h3 class="font-14 mb-24">{{ trans('update.main_content') }}</h3>

            <x-landingBuilder-input
                label="{{ trans('update.pre_title') }}"
                name="contents[main_content][pre_title]"
                value="{{ (!empty($contents['main_content']) and !empty($contents['main_content']['pre_title'])) ? $contents['main_content']['pre_title'] : '' }}"
                placeholder=""
                hint=""
                className=""
            />

            <x-landingBuilder-input
                label="{{ trans('public.title') }}"
                name="contents[main_content][title]"
                value="{{ (!empty($contents['main_content']) and !empty($contents['main_content']['title'])) ? $contents['main_content']['title'] : '' }}"
                placeholder=""
                hint=""
                className=""
            />

            <x-landingBuilder-textarea
                label="{{ trans('public.description') }}"
                name="contents[main_content][description]"
                value="{{ (!empty($contents['main_content']) and !empty($contents['main_content']['description'])) ? $contents['main_content']['description'] : '' }}"
                placeholder=""
                rows="3"
                hint="{{ trans('update.suggested_about_120_characters') }}"
                className=""
            />

            {{-- Jobs --}}
            <h3 class="font-14 text-gray-500 mt-28 mb-24">{{ trans('update.jobs') }}</h3>

            <x-landingBuilder-select
                label="{{ trans('update.number_of_jobs') }}"
                name="contents[number_of_jobs]"
                value="{{ (!empty($contents) and !empty($contents['number_of_jobs'])) ? $contents['number_of_jobs'] : '' }}"
                :items="['4_items', '8_items']"
                hint="{{ trans('update.number_of_jobs_to_display_in_board') }}"
                className=""
            />

            {{--<x-landingBuilder-select
                label="{{ trans('update.jobs_source') }}"
                name="contents[jobs_source]"
                value="{{ (!empty($contents) and !empty($contents['jobs_source'])) ? $contents['jobs_source'] : '' }}"
                :items="['newest_jobs']"
                hint=""
                className=""
            />--}}

            <h3 class="font-14 mb-24 text-gray-500">{{ trans('update.cta_section') }}</h3>

            <x-landingBuilder-input
                label="{{ trans('update.title_bold_text') }}"
                name="contents[cta_section][title_bold_text]"
                value="{{ (!empty($contents['cta_section']) and !empty($contents['cta_section']['title_bold_text'])) ? $contents['cta_section']['title_bold_text'] : '' }}"
                placeholder=""
                hint=""
                className=""
            />

            <x-landingBuilder-input
                label="{{ trans('update.title_regular_text') }}"
                name="contents[cta_section][title_regular_text]"
                value="{{ (!empty($contents['cta_section']) and !empty($contents['cta_section']['title_regular_text'])) ? $contents['cta_section']['title_regular_text'] : '' }}"
                placeholder=""
                hint=""
                className=""
            />

            <x-landingBuilder-textarea
                label="{{ trans('public.description') }}"
                name="contents[cta_section][description]"
                value="{{ (!empty($contents['cta_section']) and !empty($contents['cta_section']['description'])) ? $contents['cta_section']['description'] : '' }}"
                placeholder=""
                rows="3"
                hint="{{ trans('update.suggested_about_120_characters') }}"
                className=""
            />

            <x-landingBuilder-icons-select
                label="{{ trans('update.icon') }}"
                name="contents[cta_section][icon]"
                value="{{ (!empty($contents['cta_section']) and !empty($contents['cta_section']['icon'])) ? $contents['cta_section']['icon'] : '' }}"
                placeholder="{{ trans('update.search_icons') }}"
                hint=""
                selectClassName="js-icons-select2"
                className="mb-0"
            />

            {{-- Buttons --}}
            <x-landingBuilder-make-button
                title="{{ trans('update.button') }}"
                inputNamePrefix="contents[buttons][primary_button]"
                :buttonData="(!empty($contents['buttons']) and !empty($contents['buttons']['primary_button'])) ? $contents['buttons']['primary_button'] : []"
                className="mt-24"
            />

            <h5 class="font-14 text-gray-500 my-24">{{ trans('update.images') }}</h5>

            <x-landingBuilder-file
                label="{{ trans('update.floating_image') }}"
                name="contents[floating_image]"
                value="{{ (!empty($contents['floating_image'])) ? $contents['floating_image'] : '' }}"
                placeholder="{{ (!empty($contents['floating_image'])) ? getFileNameByPath($contents['floating_image']) : '' }}"
                hint="{{ trans('update.preferred_size') }} 208x208px"
                icon="export"
                accept="image/*"
                className=""
            />

            <x-landingBuilder-file
                label="{{ trans('update.bottom_image') }}"
                name="contents[bottom_image]"
                value="{{ (!empty($contents['bottom_image'])) ? $contents['bottom_image'] : '' }}"
                placeholder="{{ (!empty($contents['bottom_image'])) ? getFileNameByPath($contents['bottom_image']) : '' }}"
                hint="{{ trans('update.preferred_size') }} 1464x336px"
                icon="export"
                accept="image/*"
                className="mb-0"
            />
        </div>

    </div>{{-- End Col --}}

    <div class="col-12 col-lg-6 mt-20">

        {{-- Links  --}}
        <div class="p-16 rounded-16 border-gray-200 ">
            <x-landingBuilder-addable-accordions
                title="{{ trans('update.links') }}"
                addText="{{ trans('update.add_link') }}"
                className="mb-0"
                mainRow="js-link-item-main-card"
            >
                @if(!empty($contents) and !empty($contents['specific_links']) and count($contents['specific_links']))
                    @foreach($contents['specific_links'] as $sKey => $itemData)
                        @if($sKey != 'record')
                            <x-landingBuilder-accordion
                                title="{{ (!empty($itemData) and !empty($itemData['title'])) ? $itemData['title'] : trans('update.link') }}"
                                id="link_{{ $sKey }}"
                                className=""
                                show=""
                            >
                                @include('landingBuilder.admin.components.manage.jobs_board.link',['itemKey' => $sKey, 'linkData' => $itemData])
                            </x-landingBuilder-accordion>
                        @endif
                    @endforeach
                @endif
            </x-landingBuilder-addable-accordions>
        </div>

    </div>{{-- End Col --}}

</div>{{-- End Row --}}


<div class="js-link-item-main-card d-none">
    <x-landingBuilder-accordion
        title="{{ trans('update.new_link') }}"
        id="record"
        className=""
        show="true"
    >
        @include('landingBuilder.admin.components.manage.jobs_board.link')
    </x-landingBuilder-accordion>
</div>
