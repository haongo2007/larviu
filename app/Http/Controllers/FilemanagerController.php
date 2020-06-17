<?
namespace App\Http;

use Illuminate\Http\Request;
use Alexusmai\LaravelFileManager\Services\ConfigService\ConfigRepository;

class FilemanagerController implements ConfigRepository
{
    // implement all methods from interface
    
    /**
     * Get disk list
     *
     * ['public', 'local', 's3']
     *
     * @return array
     */
    public function getDiskList():array{
        return ['public'];
    }

}