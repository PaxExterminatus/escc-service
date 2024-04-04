import {UserData} from '../../model/AuthUser/api/UserData';
import {ClientData} from '../../model/AuthUser/api/ClientData';

interface AuthState
{
    user: UserData
    client: ClientData
}
