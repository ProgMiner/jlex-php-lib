<?php declare(strict_types=1);

/* MIT License

Copyright (c) 2018 Eridan Domoratskiy

Copyright (c) 2006 Wez Furlong, OmniTI Computer Consulting, Inc.

Copyright (c) 1996-2000 Elliot Joel Berk and C. Scott Ananian

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE. */

namespace JLexPHP;

/**
 * @author Wez Furlong
 * @author Eridan Domoratskiy
 */
abstract class AbstractLexer {
    const YY_F          = -1;
    const YY_NO_STATE   = -1;
    const YY_NOT_ACCEPT = 0;
    const YY_START      = 1;
    const YY_END        = 2;
    const YY_NO_ANCHOR  = 4;
    const YYEOF         = -1;

    protected static $yy_error_string = [
        'INTERNAL' => "Error: Internal error.\n",
        'MATCH'    => "Error: Unmatched input.\n"
    ];

    protected $YY_BOL;
    protected $YY_EOF;

    /** @var resource Source of code */
    protected $yy_reader;

    /** @var string */
    protected $yy_buffer = '';
    /** @var int */
    protected $yy_buffer_read = 0;
    /** @var int */
    protected $yy_buffer_index = 0;
    /** @var int */
    protected $yy_buffer_start = 0;
    /** @var int */
    protected $yy_buffer_end = 0;

    /** @var int */
    protected $yychar = 0;
    /** @var int Current column */
    protected $yycol = 0;
    /** @var int Current line */
    protected $yyline = 1;

    protected $yy_lexical_state;

    /** @var bool */
    protected $yy_at_bol = true;

    /** @var bool The last processed symbol was CR? */
    protected $yy_last_was_cr = false;
    /** @var bool */
    protected $yy_count_lines = false;
    /** @var bool */
    protected $yy_count_chars = false;

    /**
     * @param resource|string $stream Source of code
     */
    public function __construct($stream) {
        if (is_string($stream)) {
            $stream = fopen('data://text/plain,'.$stream, 'r');
        }

        $this->yy_reader = $stream;
    }

    /**
     * Creates an annotated token.
     *
     * @param int|null $type Token type
     *
     * @return Token
     */
    public function createToken(?int $type = null): Token {
        if (is_null($type)) {
            $type = ord($this->yytext());
        }

        return $this->annotateToken(new Token(['type' => $type]));
    }

    /**
     * Annotates a token with a value and source positioning.
     *
     * @param Token $token Token for annotating
     *
     * @return Token Annotated token
     */
    public function annotateToken(Token $token): Token {
        $ret = clone $token;

        $ret->value = $this->yytext();
        $ret->col = $this->yycol;
        $ret->line = $this->yyline;

        return $ret;
    }

    protected function yybegin($state) {
        $this->yy_lexical_state = $state;
    }

    protected function yy_advance() {
        if ($this->yy_buffer_index < $this->yy_buffer_read) {
            if (!isset($this->yy_buffer[$this->yy_buffer_index])) {
                return static::YY_EOF;
            }

            return ord($this->yy_buffer[$this->yy_buffer_index++]);
        }

        if ($this->yy_buffer_start !== 0) {
            /* shunt */

            $j = $this->yy_buffer_read - $this->yy_buffer_start;
            $this->yy_buffer = substr($this->yy_buffer, $this->yy_buffer_start, $j);
            $this->yy_buffer_end -= $this->yy_buffer_start;
            $this->yy_buffer_start = 0;
            $this->yy_buffer_read = $j;
            $this->yy_buffer_index = $j;

            $data = fread($this->yy_reader, 8192);
            if ($data === false || strlen($data) === 0) {
                return static::YY_EOF;
            }

            $this->yy_buffer .= $data;
            $this->yy_buffer_read += strlen($data);
        }

        while ($this->yy_buffer_index >= $this->yy_buffer_read) {
            $data = fread($this->yy_reader, 8192);

            if ($data === false || strlen($data) === 0) {
                return static::YY_EOF;
            }

            $this->yy_buffer .= $data;
            $this->yy_buffer_read += strlen($data);
        }

        return ord($this->yy_buffer[$this->yy_buffer_index++]);
    }

    protected function yy_move_end() {
        if ($this->yy_buffer_end > $this->yy_buffer_start && (
            $this->yy_buffer[$this->yy_buffer_end-1] === "\n" ||
            $this->yy_buffer[$this->yy_buffer_end-1] === "\r"
        )) {
            $this->yy_buffer_end--;
        }
    }

    protected function yy_mark_start() {
        if ($this->yy_count_lines) {
            for ($i = $this->yy_buffer_start; $i < $this->yy_buffer_index; ++$i) {
                if (!$this->yy_last_was_cr && "\n" === $this->yy_buffer[$i]) {
                    $this->yycol = 0;
                    ++$this->yyline;
                }

                if ("\r" === $this->yy_buffer[$i]) {
                    $this->yycol = 0;
                    ++$yyline;

                    $this->yy_last_was_cr = true;
                } else {
                    $this->yy_last_was_cr = false;
                }
            }
        }

        if ($this->yy_count_chars) {
            $this->yychar += $this->yy_buffer_index - $this->yy_buffer_start;
            $this->yycol  += $this->yy_buffer_index - $this->yy_buffer_start;
        }

        $this->yy_buffer_start = $this->yy_buffer_index;
    }

    protected function yy_mark_end() {
        $this->yy_buffer_end = $this->yy_buffer_index;
    }

    protected function yy_to_mark() {
        // echo "yy_to_mark: setting buffer index to ", $this->yy_buffer_end, "\n";

        $this->yy_buffer_index = $this->yy_buffer_end;

        $this->yy_at_bol = ($this->yy_buffer_end > $this->yy_buffer_start) && (
            "\r" == $this->yy_buffer[$this->yy_buffer_end-1] ||
            "\n" == $this->yy_buffer[$this->yy_buffer_end-1] ||
            2028 /* unicode LS */ == $this->yy_buffer[$this->yy_buffer_end-1] ||
            2029 /* unicode PS */ == $this->yy_buffer[$this->yy_buffer_end-1]
        );
    }

    protected function yytext() {
        return substr(
            $this->yy_buffer,
            $this->yy_buffer_start,
            $this->yy_buffer_end - $this->yy_buffer_start
        );
    }

    protected function yylength() {
        return $this->yy_buffer_end - $this->yy_buffer_start;
    }

    protected function yy_error($code, bool $fatal) {
        echo self::$yy_error_string[$code];
        flush();

        if ($fatal) {
            throw new \Exception('JLexPHP fatal error '.self::$yy_error_string[$code]);
        }
    }
}
