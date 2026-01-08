<script setup lang="ts">
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { useActiveUrl } from '@/composables/useActiveUrl';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    items: NavItem[];
}>();

const { urlIsActive } = useActiveUrl();
const expandedItems = ref<string[]>([]);

const toggleSubmenu = (title: string) => {
    const index = expandedItems.value.indexOf(title);
    if (index > -1) {
        expandedItems.value.splice(index, 1);
    } else {
        expandedItems.value.push(title);
    }
};
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <div v-if="item.submenu" class="w-full">
                    <SidebarMenuButton
                        @click="toggleSubmenu(item.title)"
                        :tooltip="item.title"
                        class="cursor-pointer"
                    >
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                        <ChevronRight
                            class="ml-auto transition-transform"
                            :class="
                                expandedItems.includes(item.title) && 'rotate-90'
                            "
                        />
                    </SidebarMenuButton>
                    <SidebarMenuSub
                        v-if="expandedItems.includes(item.title)"
                    >
                        <SidebarMenuSubItem
                            v-for="subitem in item.submenu"
                            :key="subitem.title"
                        >
                            <SidebarMenuSubButton
                                as-child
                                :is-active="
                                    urlIsActive(
                                        typeof subitem.href === 'string'
                                            ? subitem.href
                                            : subitem.href?.url
                                    )
                                "
                            >
                                <Link :href="subitem.href">
                                    <component :is="subitem.icon" />
                                    <span>{{ subitem.title }}</span>
                                </Link>
                            </SidebarMenuSubButton>
                        </SidebarMenuSubItem>
                    </SidebarMenuSub>
                </div>
                <div v-else>
                    <SidebarMenuButton
                        as-child
                        :is-active="
                            urlIsActive(
                                typeof item.href === 'string'
                                    ? item.href
                                    : item.href?.url
                            )
                        "
                        :tooltip="item.title"
                    >
                        <Link :href="item.href">
                            <component :is="item.icon" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </div>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
