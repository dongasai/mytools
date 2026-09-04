# AI Ask 问题表迁移文件

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_asks', function (Blueprint $table) {
            $table->id();
            $table->string('ask_id')->unique()->comment('问题唯一ID');
            $table->string('workflow_id')->index()->comment('Workflow ID');
            $table->string('job_id')->nullable()->comment('队列 Job ID');

            // 问题内容
            $table->text('question');
            $table->text('context')->nullable();
            $table->string('default')->nullable();

            // 状态
            $table->enum('status', ['pending', 'answered', 'timeout', 'cancelled'])
                ->default('pending')
                ->index();

            // 答案
            $table->text('answer')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->unsignedBigInteger('answered_by')->nullable();

            // 关联信息
            $table->string('agent_class')->comment('Agent 类名');
            $table->json('agent_state')->nullable()->comment('Agent 状态快照');
            $table->json('metadata')->nullable()->comment('元数据');

            // 时间
            $table->timestamp('expires_at')->nullable()->comment('过期时间');
            $table->timestamps();

            // 索引
            $table->index(['status', 'expires_at']);
            $table->index(['workflow_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_asks');
    }
};