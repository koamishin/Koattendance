<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Button } from '@/components/ui/button';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { Textarea } from '@/components/ui/textarea';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import {
    BookOpen,
    Calculator,
    ChevronRight,
    FlaskConical,
    Globe,
    Languages,
    LayoutGrid,
    Microscope,
    Music,
    Palette,
    Plus,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import AppLogo from './AppLogo.vue';

// Icon Map for dynamic rendering
const iconMap: Record<string, any> = {
    BookOpen,
    Calculator,
    FlaskConical,
    Globe,
    Languages,
    Microscope,
    Music,
    Palette,
};

const page = usePage();
const user = computed(() => page.props.auth.user);
const roles = computed(() => page.props.auth.roles);

// Get teacher's subjects from the user prop (loaded via middleware)
const myClasses = computed(() => {
    return user.value?.teacher?.subjects || [];
});

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];

    return items;
});

// Create Class Dialog Logic
const showCreateModal = ref(false);
const isCreating = ref(false);
const newSubject = ref({
    name: '',
    description: '',
    icon: 'BookOpen',
});

const availableIcons = [
    { name: 'BookOpen', label: 'General', component: BookOpen },
    { name: 'Calculator', label: 'Math', component: Calculator },
    { name: 'FlaskConical', label: 'Chemistry', component: FlaskConical },
    { name: 'Microscope', label: 'Biology', component: Microscope },
    { name: 'Globe', label: 'Geography', component: Globe },
    { name: 'Languages', label: 'Language', component: Languages },
    { name: 'Palette', label: 'Art', component: Palette },
    { name: 'Music', label: 'Music', component: Music },
];

const createSubject = async () => {
    isCreating.value = true;
    try {
        const response = await axios.post('/api/subjects', newSubject.value);
        showCreateModal.value = false;
        newSubject.value = { name: '', description: '', icon: 'BookOpen' };

        // Refresh the page to update the sidebar list (since it comes from props)
        router.reload({ only: ['auth'] });
    } catch (error) {
        console.error('Failed to create subject', error);
    } finally {
        isCreating.value = false;
    }
};

const getSubjectIcon = (iconName?: string) => {
    if (iconName && iconMap[iconName]) {
        return iconMap[iconName];
    }
    return BookOpen;
};
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />

            <!-- My Classes Section (Collapsible) -->
            <SidebarGroup v-if="roles.isTeacher">
                <SidebarGroupLabel>Management</SidebarGroupLabel>
                <SidebarMenu>
                    <Collapsible as-child class="group/collapsible">
                        <SidebarMenuItem>
                            <CollapsibleTrigger as-child>
                                <SidebarMenuButton tooltip="My Classes">
                                    <BookOpen />
                                    <span>My Classes</span>
                                    <ChevronRight
                                        class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90"
                                    />
                                </SidebarMenuButton>
                            </CollapsibleTrigger>
                            <CollapsibleContent>
                                <SidebarMenuSub>
                                    <SidebarMenuSubItem>
                                        <SidebarMenuSubButton as-child>
                                            <Link href="/dashboard/subjects">
                                                <LayoutGrid
                                                    class="mr-2 h-4 w-4"
                                                />
                                                <span>All Classes</span>
                                            </Link>
                                        </SidebarMenuSubButton>
                                    </SidebarMenuSubItem>

                                    <SidebarMenuSubItem
                                        v-for="subject in myClasses"
                                        :key="subject.id"
                                    >
                                        <SidebarMenuSubButton as-child>
                                            <Link
                                                :href="`/dashboard/subjects/${subject.id}`"
                                            >
                                                <!-- Dynamic Icon Rendering -->
                                                <component
                                                    :is="
                                                        getSubjectIcon(
                                                            subject.icon,
                                                        )
                                                    "
                                                    class="mr-2 h-4 w-4"
                                                />
                                                <span>{{ subject.name }}</span>
                                            </Link>
                                        </SidebarMenuSubButton>
                                    </SidebarMenuSubItem>

                                    <SidebarMenuSubItem>
                                        <Dialog v-model:open="showCreateModal">
                                            <DialogTrigger as-child>
                                                <SidebarMenuSubButton>
                                                    <Plus
                                                        class="mr-2 h-4 w-4"
                                                    />
                                                    <span>Create New</span>
                                                </SidebarMenuSubButton>
                                            </DialogTrigger>
                                            <DialogContent>
                                                <DialogHeader>
                                                    <DialogTitle
                                                        >Create New
                                                        Class</DialogTitle
                                                    >
                                                </DialogHeader>
                                                <div class="space-y-4 py-4">
                                                    <div class="space-y-2">
                                                        <Label
                                                            >Class Name</Label
                                                        >
                                                        <Input
                                                            v-model="
                                                                newSubject.name
                                                            "
                                                            placeholder="e.g. Mathematics 101"
                                                        />
                                                    </div>

                                                    <!-- Icon Selector -->
                                                    <div class="space-y-2">
                                                        <Label>Icon</Label>
                                                        <div
                                                            class="grid grid-cols-4 gap-2"
                                                        >
                                                            <div
                                                                v-for="icon in availableIcons"
                                                                :key="icon.name"
                                                                @click="
                                                                    newSubject.icon =
                                                                        icon.name
                                                                "
                                                                :class="[
                                                                    'flex cursor-pointer flex-col items-center justify-center rounded-md border p-2 transition-all hover:bg-muted',
                                                                    newSubject.icon ===
                                                                    icon.name
                                                                        ? 'border-primary bg-primary/10 text-primary ring-2 ring-primary/20'
                                                                        : 'border-input',
                                                                ]"
                                                            >
                                                                <component
                                                                    :is="
                                                                        icon.component
                                                                    "
                                                                    class="mb-1 h-5 w-5"
                                                                />
                                                                <span
                                                                    class="w-full truncate text-center text-[10px]"
                                                                    >{{
                                                                        icon.label
                                                                    }}</span
                                                                >
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="space-y-2">
                                                        <Label
                                                            >Description
                                                            (Optional)</Label
                                                        >
                                                        <Textarea
                                                            v-model="
                                                                newSubject.description
                                                            "
                                                            placeholder="Class description..."
                                                        />
                                                    </div>
                                                </div>
                                                <DialogFooter>
                                                    <Button
                                                        variant="outline"
                                                        @click="
                                                            showCreateModal = false
                                                        "
                                                        >Cancel</Button
                                                    >
                                                    <Button
                                                        @click="createSubject"
                                                        :disabled="isCreating"
                                                    >
                                                        {{
                                                            isCreating
                                                                ? 'Creating...'
                                                                : 'Create Class'
                                                        }}
                                                    </Button>
                                                </DialogFooter>
                                            </DialogContent>
                                        </Dialog>
                                    </SidebarMenuSubItem>
                                </SidebarMenuSub>
                            </CollapsibleContent>
                        </SidebarMenuItem>
                    </Collapsible>
                </SidebarMenu>
            </SidebarGroup>
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="[]" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
