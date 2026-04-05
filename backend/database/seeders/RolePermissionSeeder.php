<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        $permissionGroups = [
            'Usuários' => [
                ['name' => 'view_users', 'display_name' => 'Visualizar Usuários'],
                ['name' => 'create_user', 'display_name' => 'Criar Usuário'],
                ['name' => 'edit_user', 'display_name' => 'Editar Usuário'],
                ['name' => 'delete_user', 'display_name' => 'Excluir Usuário'],
                ['name' => 'invite_user', 'display_name' => 'Convidar Usuário'],
            ],
            'Tópicos' => [
                ['name' => 'view_topics', 'display_name' => 'Visualizar Tópicos'],
                ['name' => 'create_topic', 'display_name' => 'Criar Tópico'],
                ['name' => 'edit_own_topic', 'display_name' => 'Editar Próprio Tópico'],
                ['name' => 'edit_any_topic', 'display_name' => 'Editar Qualquer Tópico'],
                ['name' => 'delete_own_topic', 'display_name' => 'Excluir Próprio Tópico'],
                ['name' => 'delete_any_topic', 'display_name' => 'Excluir Qualquer Tópico'],
                ['name' => 'pin_topic', 'display_name' => 'Fixar Tópico'],
                ['name' => 'lock_topic', 'display_name' => 'Bloquear Tópico'],
            ],
            'Comentários' => [
                ['name' => 'create_interaction', 'display_name' => 'Comentar'],
                ['name' => 'edit_own_interaction', 'display_name' => 'Editar Próprio Comentário'],
                ['name' => 'edit_any_interaction', 'display_name' => 'Editar Qualquer Comentário'],
                ['name' => 'delete_own_interaction', 'display_name' => 'Excluir Próprio Comentário'],
                ['name' => 'delete_any_interaction', 'display_name' => 'Excluir Qualquer Comentário'],
            ],
            'Biblioteca' => [
                ['name' => 'view_works', 'display_name' => 'Visualizar Obras'],
                ['name' => 'suggest_work', 'display_name' => 'Sugerir Obra'],
                ['name' => 'approve_work', 'display_name' => 'Aprovar Obra'],
                ['name' => 'edit_work', 'display_name' => 'Editar Obra'],
                ['name' => 'delete_work', 'display_name' => 'Excluir Obra'],
            ],
            'Arquivos' => [
                ['name' => 'upload_asset', 'display_name' => 'Enviar Arquivo'],
                ['name' => 'download_asset', 'display_name' => 'Baixar Arquivo'],
                ['name' => 'delete_own_asset', 'display_name' => 'Excluir Próprio Arquivo'],
                ['name' => 'delete_any_asset', 'display_name' => 'Excluir Qualquer Arquivo'],
            ],
            'Moderação' => [
                ['name' => 'moderate_content', 'display_name' => 'Moderar Conteúdo'],
                ['name' => 'ban_user', 'display_name' => 'Banir Usuário'],
                ['name' => 'shadow_ban_user', 'display_name' => 'Shadow Ban'],
            ],
            'Sistema' => [
                ['name' => 'view_settings', 'display_name' => 'Visualizar Configurações'],
                ['name' => 'edit_settings', 'display_name' => 'Alterar Configurações'],
                ['name' => 'manage_roles', 'display_name' => 'Gerenciar Cargos'],
                ['name' => 'view_logs', 'display_name' => 'Visualizar Logs'],
            ],
            'Relatórios' => [
                ['name' => 'view_reports', 'display_name' => 'Visualizar Relatórios'],
                ['name' => 'export_data', 'display_name' => 'Exportar Dados'],
            ],
        ];

        $allPermIds = [];

        foreach ($permissionGroups as $groupName => $permissions) {
            foreach ($permissions as $permData) {
                $perm = Permission::firstOrCreate(['name' => $permData['name']], [
                    'display_name' => $permData['display_name'],
                    'description' => 'Permissão do grupo ' . $groupName,
                ]);
                $allPermIds[] = $perm->id;
            }
        }

        $owner = Role::firstOrCreate(['name' => 'owner'], [
            'display_name' => 'Dono',
            'description' => 'Acesso total ao sistema',
        ]);
        $owner->permissions()->sync($allPermIds);

        $moderator = Role::firstOrCreate(['name' => 'moderator'], [
            'display_name' => 'Moderador',
            'description' => 'Moderação de conteúdo e usuários',
        ]);
        $modPerms = array_merge(
            array_slice($allPermIds, 0, 5),
            array_slice($allPermIds, 11, 5),
            array_slice($allPermIds, 22, 3)
        );
        $moderator->permissions()->sync($modPerms);

        $editor = Role::firstOrCreate(['name' => 'editor'], [
            'display_name' => 'Editor',
            'description' => 'Gerenciamento de tópicos e biblioteca',
        ]);
        $editorPerms = array_slice($allPermIds, 5, 11);
        $editor->permissions()->sync($editorPerms);

        $member = Role::firstOrCreate(['name' => 'member'], [
            'display_name' => 'Membro',
            'description' => 'Usuário padrão com acesso básico',
        ]);
        $memberPerms = [
            $allPermIds[0], 
            $allPermIds[5], 
            $allPermIds[11], 
            $allPermIds[16], 
            $allPermIds[21]
        ];
        $member->permissions()->sync($memberPerms);

        $this->command->info('Cargos e permissões criados com sucesso!');
    }
}