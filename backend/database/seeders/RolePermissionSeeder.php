<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Criar cargo Dono (imutável)
        $owner = Role::firstOrCreate([
            'name' => 'owner',
        ], [
            'display_name' => 'Dono',
            'description' => 'Cargo imutável com todas as permissões',
        ]);

        // Lista completa de permissões
        $permissions = [
            // Usuários
            ['name' => 'view_users', 'display_name' => 'Visualizar Usuários', 'description' => 'Permite visualizar lista de usuários'],
            ['name' => 'create_user', 'display_name' => 'Criar Usuário', 'description' => 'Permite criar novo usuário'],
            ['name' => 'edit_user', 'display_name' => 'Editar Usuário', 'description' => 'Permite editar dados de usuário'],
            ['name' => 'delete_user', 'display_name' => 'Excluir Usuário', 'description' => 'Permite excluir usuário'],
            ['name' => 'invite_user', 'display_name' => 'Convidar Usuário', 'description' => 'Permite enviar convite para novo usuário'],

            // Tópicos
            ['name' => 'view_topics', 'display_name' => 'Visualizar Tópicos', 'description' => 'Permite visualizar tópicos públicos'],
            ['name' => 'create_topic', 'display_name' => 'Criar Tópico', 'description' => 'Permite criar novo tópico'],
            ['name' => 'edit_own_topic', 'display_name' => 'Editar Próprio Tópico', 'description' => 'Permite editar próprio tópico'],
            ['name' => 'edit_any_topic', 'display_name' => 'Editar Qualquer Tópico', 'description' => 'Permite editar qualquer tópico'],
            ['name' => 'delete_own_topic', 'display_name' => 'Excluir Próprio Tópico', 'description' => 'Permite excluir próprio tópico'],
            ['name' => 'delete_any_topic', 'display_name' => 'Excluir Qualquer Tópico', 'description' => 'Permite excluir qualquer tópico'],
            ['name' => 'pin_topic', 'display_name' => 'Fixar Tópico', 'description' => 'Permite fixar tópico no topo'],
            ['name' => 'lock_topic', 'display_name' => 'Bloquear Tópico', 'description' => 'Permite bloquear tópico para comentários'],

            // Interações
            ['name' => 'create_interaction', 'display_name' => 'Comentar', 'description' => 'Permite comentar em tópicos'],
            ['name' => 'edit_own_interaction', 'display_name' => 'Editar Próprio Comentário', 'description' => 'Permite editar próprio comentário'],
            ['name' => 'edit_any_interaction', 'display_name' => 'Editar Qualquer Comentário', 'description' => 'Permite editar qualquer comentário'],
            ['name' => 'delete_own_interaction', 'display_name' => 'Excluir Próprio Comentário', 'description' => 'Permite excluir próprio comentário'],
            ['name' => 'delete_any_interaction', 'display_name' => 'Excluir Qualquer Comentário', 'description' => 'Permite excluir qualquer comentário'],

            // Biblioteca (Obras)
            ['name' => 'view_works', 'display_name' => 'Visualizar Obras', 'description' => 'Permite visualizar catálogo de obras'],
            ['name' => 'suggest_work', 'display_name' => 'Sugerir Obra', 'description' => 'Permite sugerir nova obra'],
            ['name' => 'approve_work', 'display_name' => 'Aprovar Obra', 'description' => 'Permite aprovar obra sugerida'],
            ['name' => 'edit_work', 'display_name' => 'Editar Obra', 'description' => 'Permite editar informações de obra'],
            ['name' => 'delete_work', 'display_name' => 'Excluir Obra', 'description' => 'Permite excluir obra'],

            // Acervo & Arquivos
            ['name' => 'upload_asset', 'display_name' => 'Enviar Arquivo', 'description' => 'Permite enviar arquivo'],
            ['name' => 'download_asset', 'display_name' => 'Baixar Arquivo', 'description' => 'Permite baixar arquivo'],
            ['name' => 'delete_own_asset', 'display_name' => 'Excluir Próprio Arquivo', 'description' => 'Permite excluir próprio arquivo'],
            ['name' => 'delete_any_asset', 'display_name' => 'Excluir Qualquer Arquivo', 'description' => 'Permite excluir qualquer arquivo'],

            // Moderação
            ['name' => 'moderate_content', 'display_name' => 'Moderar Conteúdo', 'description' => 'Permite moderar conteúdo (aprovar/rejeitar)'],
            ['name' => 'ban_user', 'display_name' => 'Banir Usuário', 'description' => 'Permite banir usuário'],
            ['name' => 'shadow_ban_user', 'display_name' => 'Shadow Ban', 'description' => 'Permite silenciar usuário sem avisar'],
            ['name' => 'view_moderation_log', 'display_name' => 'Ver Log de Moderação', 'description' => 'Permite visualizar log de moderação'],

            // Sistema & Configurações
            ['name' => 'view_settings', 'display_name' => 'Visualizar Configurações', 'description' => 'Permite visualizar configurações do sistema'],
            ['name' => 'edit_settings', 'display_name' => 'Alterar Configurações', 'description' => 'Permite alterar configurações do sistema'],
            ['name' => 'manage_roles', 'display_name' => 'Gerenciar Cargos', 'description' => 'Permite gerenciar cargos e permissões'],
            ['name' => 'view_logs', 'display_name' => 'Visualizar Logs', 'description' => 'Permite visualizar logs do sistema'],

            // Relatórios & Estatísticas
            ['name' => 'view_reports', 'display_name' => 'Visualizar Relatórios', 'description' => 'Permite visualizar relatórios'],
            ['name' => 'export_data', 'display_name' => 'Exportar Dados', 'description' => 'Permite exportar dados (PDF, CSV, etc)'],
        ];

        foreach ($permissions as $permData) {
            $perm = Permission::firstOrCreate(['name' => $permData['name']], $permData);
            // Atribuir todas as permissões ao Dono
            $owner->permissions()->syncWithoutDetaching($perm);
        }
    }
}