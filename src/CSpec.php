<?php

/* Copyright 2018 Eridan Domoratskiy
Based on JLexPHP which is:

    Copyright 2006 Wez Furlong, OmniTI Computer Consulting, Inc.
    Based on JLex which is:

                 JLEX COPYRIGHT NOTICE, LICENSE, AND DISCLAIMER
        Copyright 1996-2000 by Elliot Joel Berk and C. Scott Ananian

        Permission to use, copy, modify, and distribute this software and its
        documentation for any purpose and without fee is hereby granted,
        provided that the above copyright notice appear in all copies and that
        both the copyright notice and this permission notice and warranty
        disclaimer appear in supporting documentation, and that the name of
        the authors or their employers not be used in advertising or publicity
        pertaining to distribution of the software without specific, written
        prior permission.

        The authors and their employers disclaim all warranties with regard to
        this software, including all implied warranties of merchantability and
        fitness. In no event shall the authors or their employers be liable
        for any special, indirect or consequential damages or any damages
        whatsoever resulting from loss of use, data or profits, whether in an
        action of contract, negligence or other tortious action, arising out
        of or in connection with the use or performance of this software. */

namespace PHPLex;

/**
 * @author Eridan Domoratskiy
 */
class CSpec {

    const NONE  = 0;
    const START = 1;
    const END   = 2;

    /**
     * Special pseudo-characters for beginning-of-line and end-of-file.
     */
    const NUM_PSEUDO = 2;

    /** @var mixed Beginning-of-line */
    public $BOL;

    /** @var mixed Beginning-of-line */
    public $EOF;

    /**
     * Lexical States
     */

    /** @var string[] Hashtable taking state indices (Integer)
     *               to state name (String). */
    public $m_states = [];

    /**
     * Regular Expression Macros.
     */

    /**
     * @var array Hashtable taking macro name (String)
     *            to corresponding char buffer that
     *            holds macro definition.
     */
    public $m_macros = [];

    /**
     * NFA Machine.
     */

    /** @var CNfa Start state of NFA machine. */
    public $m_nfa_start = null;

    /**
     * @var array Vector of states, with index
     *            corresponding to label.
     */
    public $m_nfa_states = [];

    /**
     * @var array[] An array of Vectors of Integers.
     *
     *              The ith Vector represents the lexical state
     *              with index i. The contents of the ith
     *              Vector are the indices of the NFA start
     *              states that can be matched while in
     *              the ith lexical state.
     */
    public $m_state_rules = null;

    /** @var int[] */
    public $m_state_dtrans = null;

    /**
     * DFA Machine.
     */

    /**
     * @var array Vector of states, with index
     *            corresponding to label.
     */
    public $m_dfa_states = [];

    /**
     * @var array Hashtable taking set of NFA states
     *            to corresponding DFA state,
     *            if the latter exists.
     */
    public $m_dfa_sets = [];

    /** @var array Accept States. */
    public $m_accept_vector = null;

    /** @var int[] Corresponding Anchors. */
    public $m_anchor_array = null;

    /**
     * Transition Table.
     */

    /** @var array */
    public $m_dtrans_vector = [];

    /** @var int */
    public $m_dtrans_ncols;

    /** @var int[] */
    public $m_row_map = null;

    /** @var int[] */
    public $m_col_map = null;

    /** @var int[] NFA character class minimization map. */
    public $m_ccls_map;

    /**
     * Regular expression token variables.
     */

    /** @var int */
    public $m_current_token;

    /** @var string */
    public $m_lexeme;

    /** @var bool */
    public $m_in_quote;

    /** @var bool */
    public $m_in_ccl;

    /** @var bool Verbose execution flag.*/
    public $m_verbose = true;

    /**
     * PHPLex directives flags.
     */

    /** @var bool */
    public $m_integer_type = false;

    /** @var bool */
    public $m_intwrap_type = false;

    /** @var bool */
    public $m_yyeof = false;

    /** @var bool */
    public $m_count_chars = false;

    /** @var bool */
    public $m_count_lines = false;

    /** @var bool */
    public $m_cup_compatible = false;

    /** @var bool */
    public $m_unix = true;

    /** @var bool */
    public $m_public = false;

    /** @var bool */
    public $m_ignorecase = false;

    /** @var string[] */
    public $m_init_code = null;

    /** @var int */
    public $m_init_read = 0;

    /** @var string[] */
    public $m_init_throw_code = null;

    /** @var int */
    public $m_init_throw_read = 0;

    /** @var string[] */
    public $m_class_code = null;

    /** @var int */
    public $m_class_read = 0;

    /** @var string[] */
    public $m_eof_code = null;

    /** @var int */
    public $m_eof_read = 0;

    /** @var string[] */
    public $m_eof_value_code = null;

    /** @var int */
    public $m_eof_value_read = 0;

    /** @var string[] */
    public $m_eof_throw_code = null;

    /** @var int */
    public $m_eof_throw_read = 0;

    /** @var string[] */
    public $m_yylex_throw_code = null;

    /** @var int */
    public $m_yylex_throw_read = 0;

    /**
     * Class, function, type names.
     */

    /** @var string[] */
    public $m_class_name = ['Y', 'y', 'l', 'e', 'x'];

    /** @var string[] */
    public $m_implements_name = [];

    /** @var string[] */
    public $m_function_name[] = ['y', 'y', 'l', 'e', 'x'];

    /** @var string[] */
    public $m_type_name[] = ['Y', 'y', 't', 'o', 'k', 'e', 'n'];

    /** @var CLexGen Lexical Generator. */
    private $m_lexGen;

    /**
     * @param CLexGen $lexGen Lexical Generator.
     */
    public function __construct(CLexGen $lexGen) {
        $this->m_lexGen = $lexGen;

        // Initialize regular expression token variables.
        $this->m_current_token = $lexGen->EOS;
        $this->m_lexeme = '\0';
        $this->m_in_quote = false;
        $this->m_in_ccl = false;

        // Initialize hashtable for lexer states.
        $this->m_states['YYINITIAL'] = count($this->m_states);

        $this->m_dtrans_ncols = CUtility::MAX_SEVEN_BIT + 1;
    }
}
