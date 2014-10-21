<?php
use Rhumsaa\Uuid\Console\Exception;
use RM_Cassandra_Session as Session;

class RM_Cassandra_SessionStore
    implements
        Zend_Session_SaveHandler_Interface {

    /**
     * Open Session - retrieve resources
     *
     * @param string $save_path
     * @param string $name
     * @return bool
     */
    public function open($save_path, $name) {
        return true;
    }

    /**
     * Close Session - free resources
     *
     */
    public function close() {
        return true;
    }

    /**
     * Read session data
     *
     * @param string $id
     * @return string
     */
    public function read($id) {
        $session = Session::byId($id);
        return $session instanceof Session ? stripcslashes($session->getData()) : '';
    }

    /**
     * Write Session - commit data to resource
     *
     * @param string $id
     * @param mixed $data
     * @return bool
     */
    public function write($id, $data) {
        $session = Session::byId($id);
        if (!$session instanceof Session) {
            $session = new Session(['id' => $id]);
        }
        $session->setData($data);
        $session->setModifiedAt(time());
        try {
            $session->save();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Destroy Session - remove data from resource for
     * given session id
     *
     * @param string $id
     * @return bool
     */
    public function destroy($id) {
        $delete = new RM_Cassandra_Query_Delete(Session::TABLE_NAME);
        $delete->where()->valueOf(Session::ID_NAME)->equalsTo($id);
        RM_Cassandra_Cql::exec($delete);
        return true;
    }

    /**
     * Garbage Collection - remove old session data older
     * than $maxlifetime (in seconds)
     *
     * @param int $maxlifetime
     */
    public function gc($maxlifetime) {
        // lifetime session
    }

}