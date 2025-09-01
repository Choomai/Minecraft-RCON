<?php
/**
 * See https://developer.valvesoftware.com/wiki/Source_RCON_Protocol for
 * more information about Source RCON Packets
 *
 * @copyright 2013 Chris Churchwell
 */
class Rcon {
    private $host;
    private $port;
    private $password;
    private $timeout;

    private $socket;

    private $authorized;
    private $last_response;

    const PACKET_AUTHORIZE = 5;
    const PACKET_COMMAND = 6;

    const SERVERDATA_AUTH = 3;
    const SERVERDATA_AUTH_RESPONSE = 2;
    const SERVERDATA_EXECCOMMAND = 2;
    const SERVERDATA_RESPONSE_VALUE = 0;

    const MAX_READ_ATTEMPTS = 5;

    public function __construct($host, $port, $password, $timeout) {
        $this->host = $host;
        $this->port = $port;
        $this->password = $password;
        $this->timeout = $timeout;
    }

    public function get_response() {return $this->last_response;}

    public function connect() {
        $this->socket = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);

        if (!$this->socket) {
            $this->last_response = $errstr;
            return false;
        }

        //set timeout
        stream_set_timeout($this->socket, 3);
        //authorize
        return $this->authorize();
    }

    public function disconnect() {if ($this->socket) {fclose($this->socket);}}

    public function is_connected() {return $this->authorized;}

    public function send_command($command) {
        // Is there really multiple packets?
        if (!$this->is_connected()) {
            return false;
        }

        // send command packet
        $this->write_packet(Rcon::PACKET_COMMAND, Rcon::SERVERDATA_EXECCOMMAND, $command);
        
        // collect responses
        $response = "";
        $start_time = microtime(true);
        
        do {
            $packet = $this->read_packet();
            
            // Check for timeout (3 seconds)
            if (microtime(true) - $start_time > 3) {
                $this->last_response = "Response timeout";
                return false;
            }
            
            // Valid response packet
            if ($packet && $packet["id"] == Rcon::PACKET_COMMAND && $packet["type"] == Rcon::SERVERDATA_RESPONSE_VALUE) {
                $response .= $packet["body"];
                
                // Wait briefly for more packets
                usleep(50000); // 50ms delay
                
                // Check if there's more data to read
                $more_data = $this->hasMoreData();
                
            } else if (empty($packet)) {
                if (empty($response)) {
                    $this->last_response = "Invalid response from server";
                    return false;
                }
                break;
            }
        } while ($more_data);

        $this->last_response = $response;
        return $response;
    }

    private function hasMoreData() {
        $read = array($this->socket);
        $write = null;
        $except = null;
        return stream_select($read, $write, $except, 0, 50000) > 0;
    }

    private function authorize() {
        $this->write_packet(Rcon::PACKET_AUTHORIZE, Rcon::SERVERDATA_AUTH, $this->password);
        $response_packet = $this->read_packet();
    
        if ($response_packet === false) {
            return false;
        }
    
        // Verify both type and ID match for proper auth
        $this->authorized = (
            $response_packet["type"] == Rcon::SERVERDATA_AUTH_RESPONSE &&
            $response_packet["id"] == Rcon::PACKET_AUTHORIZE
        );
    
        return $this->authorized;
    }

    /**
     * Writes a packet to the socket stream..
     */
    private function write_packet($packet_id, $packet_type, $packet_body) {
        /*
        Size      32-bit little-endian Signed Integer     Varies, see below.
        ID        32-bit little-endian Signed Integer    Varies, see below.
        Type      32-bit little-endian Signed Integer    Varies, see below.
        Body      Null-terminated ASCII String      Varies, see below.
        Empty String  Null-terminated ASCII String      0x00
        */
        
        // Validate packet size (RCON limit is 4096 bytes)
        $packet = pack("VV", $packet_id, $packet_type);
        $packet .= $packet_body . "\x00";
        $packet .= "\x00";
        
        if (strlen($packet) > 4096) {
            throw new \Exception("RCON packet exceeds maximum size of 4096 bytes");
        }

        $packet_size = strlen($packet);
        $packet = pack("V", $packet_size) . $packet;

        if (fwrite($this->socket, $packet, strlen($packet)) === false) {
            throw new \Exception("Failed to write to RCON socket");
        }
    }

    private function read_packet() {
        $size_data = fread($this->socket, 4);
        $size_pack = unpack("V1size", $size_data);
        $size = $size_pack["size"];
        
        if ($size < 10) return null;

        $packet = @fread($this->socket, $size);
        return unpack("V1id/V1type/a*body", $packet);
    }

    public function __destruct() {
        fclose($this->socket);
    }
}
?>