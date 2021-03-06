--TEST--
insert/update/delete
--SKIPIF--
--FILE--
<?php
require_once dirname(__FILE__) . '/../common/config.php';

$mysql = get_mysql_connection();

init_mysql_testdb($mysql);

$table = 'hstesttbl';
$tablesize = 256;
$sql = sprintf(
    'CREATE TABLE %s ( ' .
    'k varchar(30) PRIMARY KEY, ' .
    'v1 varchar(30), ' .
    'v2 varchar(30)) ' .
    'Engine = innodb default charset = binary',
    mysql_real_escape_string($table));
if (!mysql_query($sql, $mysql))
{
    die(mysql_error());
}

srand(999);

$valmap = array();

echo 'HSINSERT', PHP_EOL;
$hs = new HandlerSocket(MYSQL_HOST, MYSQL_HANDLERSOCKET_PORT_WR);
if (!($hs->openIndex(1, MYSQL_DBNAME, $table, '', 'k,v1,v2')))
{
    die();
}

for ($i = 0; $i < $tablesize; $i++)
{
    $k = (string)$i;
    $v1 = 'v1_' . $i;
    $v2 = 'v2_' . $i;

    $retval = $hs->executeSingle(1, '+', array($k, $v1, $v2));
    if ($retval === false)
    {
        echo $hs->getError(), PHP_EOL;
    }
}

unset($hs);
dump_table($mysql, $table);


echo 'HSUPDATE', PHP_EOL;
$hs = new HandlerSocket(MYSQL_HOST, MYSQL_HANDLERSOCKET_PORT_WR);
if (!($hs->openIndex(2, MYSQL_DBNAME, $table, '', 'v1')))
{
    die();
}

for ($i = 0; $i < $tablesize; $i++)
{
    $k = (string)$i;
    if (version_compare(PHP_VERSION, '5.1.0', '>='))
    {
        $retval = $hs->executeSingle(
            2, '=', array($k), 1000, 0,
            HandlerSocket::UPDATE, array('mod_' . $i));
    }
    else
    {
        $retval = $hs->executeSingle(
            2, '=', array($k), 1000, 0,
            HANDLERSOCKET_UPDATE, array('mod_' . $i));
    }
    if ($retval === false)
    {
        echo $hs->getError(), PHP_EOL;
    }
}

unset($hs);
dump_table($mysql, $table);


echo 'HSDELETE', PHP_EOL;
$hs = new HandlerSocket(MYSQL_HOST, MYSQL_HANDLERSOCKET_PORT_WR);
if (!($hs->openIndex(3, MYSQL_DBNAME, $table, '', '')))
{
    die();
}

for ($i = 0; $i < $tablesize; $i = $i + 2)
{
    $k = (string)$i;
    if (version_compare(PHP_VERSION, '5.1.0', '>='))
    {
        $retval = $hs->executeSingle(
            3, '=', array($k), 1000, 0, HandlerSocket::DELETE);
    }
    else
    {
        $retval = $hs->executeSingle(
            3, '=', array($k), 1000, 0, HANDLERSOCKET_DELETE);
    }
    if ($retval === false)
    {
        echo $hs->getError(), PHP_EOL;
    }
}

unset($hs);
dump_table($mysql, $table);


function dump_table($mysql, $table)
{
    echo 'DUMP_TABLE', PHP_EOL;
    $sql = 'SELECT k,v1,v2 FROM ' . $table . ' ORDER BY k';
    $result = mysql_query($sql, $mysql);
    if ($result)
    {
        while ($row = mysql_fetch_assoc($result))
        {
            echo $row['k'], ' ', $row['v1'], ' ', $row['v2'], PHP_EOL;
        }
    }
    mysql_free_result($result);
}

mysql_close($mysql);

--EXPECT--
HSINSERT
DUMP_TABLE
0 v1_0 v2_0
1 v1_1 v2_1
10 v1_10 v2_10
100 v1_100 v2_100
101 v1_101 v2_101
102 v1_102 v2_102
103 v1_103 v2_103
104 v1_104 v2_104
105 v1_105 v2_105
106 v1_106 v2_106
107 v1_107 v2_107
108 v1_108 v2_108
109 v1_109 v2_109
11 v1_11 v2_11
110 v1_110 v2_110
111 v1_111 v2_111
112 v1_112 v2_112
113 v1_113 v2_113
114 v1_114 v2_114
115 v1_115 v2_115
116 v1_116 v2_116
117 v1_117 v2_117
118 v1_118 v2_118
119 v1_119 v2_119
12 v1_12 v2_12
120 v1_120 v2_120
121 v1_121 v2_121
122 v1_122 v2_122
123 v1_123 v2_123
124 v1_124 v2_124
125 v1_125 v2_125
126 v1_126 v2_126
127 v1_127 v2_127
128 v1_128 v2_128
129 v1_129 v2_129
13 v1_13 v2_13
130 v1_130 v2_130
131 v1_131 v2_131
132 v1_132 v2_132
133 v1_133 v2_133
134 v1_134 v2_134
135 v1_135 v2_135
136 v1_136 v2_136
137 v1_137 v2_137
138 v1_138 v2_138
139 v1_139 v2_139
14 v1_14 v2_14
140 v1_140 v2_140
141 v1_141 v2_141
142 v1_142 v2_142
143 v1_143 v2_143
144 v1_144 v2_144
145 v1_145 v2_145
146 v1_146 v2_146
147 v1_147 v2_147
148 v1_148 v2_148
149 v1_149 v2_149
15 v1_15 v2_15
150 v1_150 v2_150
151 v1_151 v2_151
152 v1_152 v2_152
153 v1_153 v2_153
154 v1_154 v2_154
155 v1_155 v2_155
156 v1_156 v2_156
157 v1_157 v2_157
158 v1_158 v2_158
159 v1_159 v2_159
16 v1_16 v2_16
160 v1_160 v2_160
161 v1_161 v2_161
162 v1_162 v2_162
163 v1_163 v2_163
164 v1_164 v2_164
165 v1_165 v2_165
166 v1_166 v2_166
167 v1_167 v2_167
168 v1_168 v2_168
169 v1_169 v2_169
17 v1_17 v2_17
170 v1_170 v2_170
171 v1_171 v2_171
172 v1_172 v2_172
173 v1_173 v2_173
174 v1_174 v2_174
175 v1_175 v2_175
176 v1_176 v2_176
177 v1_177 v2_177
178 v1_178 v2_178
179 v1_179 v2_179
18 v1_18 v2_18
180 v1_180 v2_180
181 v1_181 v2_181
182 v1_182 v2_182
183 v1_183 v2_183
184 v1_184 v2_184
185 v1_185 v2_185
186 v1_186 v2_186
187 v1_187 v2_187
188 v1_188 v2_188
189 v1_189 v2_189
19 v1_19 v2_19
190 v1_190 v2_190
191 v1_191 v2_191
192 v1_192 v2_192
193 v1_193 v2_193
194 v1_194 v2_194
195 v1_195 v2_195
196 v1_196 v2_196
197 v1_197 v2_197
198 v1_198 v2_198
199 v1_199 v2_199
2 v1_2 v2_2
20 v1_20 v2_20
200 v1_200 v2_200
201 v1_201 v2_201
202 v1_202 v2_202
203 v1_203 v2_203
204 v1_204 v2_204
205 v1_205 v2_205
206 v1_206 v2_206
207 v1_207 v2_207
208 v1_208 v2_208
209 v1_209 v2_209
21 v1_21 v2_21
210 v1_210 v2_210
211 v1_211 v2_211
212 v1_212 v2_212
213 v1_213 v2_213
214 v1_214 v2_214
215 v1_215 v2_215
216 v1_216 v2_216
217 v1_217 v2_217
218 v1_218 v2_218
219 v1_219 v2_219
22 v1_22 v2_22
220 v1_220 v2_220
221 v1_221 v2_221
222 v1_222 v2_222
223 v1_223 v2_223
224 v1_224 v2_224
225 v1_225 v2_225
226 v1_226 v2_226
227 v1_227 v2_227
228 v1_228 v2_228
229 v1_229 v2_229
23 v1_23 v2_23
230 v1_230 v2_230
231 v1_231 v2_231
232 v1_232 v2_232
233 v1_233 v2_233
234 v1_234 v2_234
235 v1_235 v2_235
236 v1_236 v2_236
237 v1_237 v2_237
238 v1_238 v2_238
239 v1_239 v2_239
24 v1_24 v2_24
240 v1_240 v2_240
241 v1_241 v2_241
242 v1_242 v2_242
243 v1_243 v2_243
244 v1_244 v2_244
245 v1_245 v2_245
246 v1_246 v2_246
247 v1_247 v2_247
248 v1_248 v2_248
249 v1_249 v2_249
25 v1_25 v2_25
250 v1_250 v2_250
251 v1_251 v2_251
252 v1_252 v2_252
253 v1_253 v2_253
254 v1_254 v2_254
255 v1_255 v2_255
26 v1_26 v2_26
27 v1_27 v2_27
28 v1_28 v2_28
29 v1_29 v2_29
3 v1_3 v2_3
30 v1_30 v2_30
31 v1_31 v2_31
32 v1_32 v2_32
33 v1_33 v2_33
34 v1_34 v2_34
35 v1_35 v2_35
36 v1_36 v2_36
37 v1_37 v2_37
38 v1_38 v2_38
39 v1_39 v2_39
4 v1_4 v2_4
40 v1_40 v2_40
41 v1_41 v2_41
42 v1_42 v2_42
43 v1_43 v2_43
44 v1_44 v2_44
45 v1_45 v2_45
46 v1_46 v2_46
47 v1_47 v2_47
48 v1_48 v2_48
49 v1_49 v2_49
5 v1_5 v2_5
50 v1_50 v2_50
51 v1_51 v2_51
52 v1_52 v2_52
53 v1_53 v2_53
54 v1_54 v2_54
55 v1_55 v2_55
56 v1_56 v2_56
57 v1_57 v2_57
58 v1_58 v2_58
59 v1_59 v2_59
6 v1_6 v2_6
60 v1_60 v2_60
61 v1_61 v2_61
62 v1_62 v2_62
63 v1_63 v2_63
64 v1_64 v2_64
65 v1_65 v2_65
66 v1_66 v2_66
67 v1_67 v2_67
68 v1_68 v2_68
69 v1_69 v2_69
7 v1_7 v2_7
70 v1_70 v2_70
71 v1_71 v2_71
72 v1_72 v2_72
73 v1_73 v2_73
74 v1_74 v2_74
75 v1_75 v2_75
76 v1_76 v2_76
77 v1_77 v2_77
78 v1_78 v2_78
79 v1_79 v2_79
8 v1_8 v2_8
80 v1_80 v2_80
81 v1_81 v2_81
82 v1_82 v2_82
83 v1_83 v2_83
84 v1_84 v2_84
85 v1_85 v2_85
86 v1_86 v2_86
87 v1_87 v2_87
88 v1_88 v2_88
89 v1_89 v2_89
9 v1_9 v2_9
90 v1_90 v2_90
91 v1_91 v2_91
92 v1_92 v2_92
93 v1_93 v2_93
94 v1_94 v2_94
95 v1_95 v2_95
96 v1_96 v2_96
97 v1_97 v2_97
98 v1_98 v2_98
99 v1_99 v2_99
HSUPDATE
DUMP_TABLE
0 mod_0 v2_0
1 mod_1 v2_1
10 mod_10 v2_10
100 mod_100 v2_100
101 mod_101 v2_101
102 mod_102 v2_102
103 mod_103 v2_103
104 mod_104 v2_104
105 mod_105 v2_105
106 mod_106 v2_106
107 mod_107 v2_107
108 mod_108 v2_108
109 mod_109 v2_109
11 mod_11 v2_11
110 mod_110 v2_110
111 mod_111 v2_111
112 mod_112 v2_112
113 mod_113 v2_113
114 mod_114 v2_114
115 mod_115 v2_115
116 mod_116 v2_116
117 mod_117 v2_117
118 mod_118 v2_118
119 mod_119 v2_119
12 mod_12 v2_12
120 mod_120 v2_120
121 mod_121 v2_121
122 mod_122 v2_122
123 mod_123 v2_123
124 mod_124 v2_124
125 mod_125 v2_125
126 mod_126 v2_126
127 mod_127 v2_127
128 mod_128 v2_128
129 mod_129 v2_129
13 mod_13 v2_13
130 mod_130 v2_130
131 mod_131 v2_131
132 mod_132 v2_132
133 mod_133 v2_133
134 mod_134 v2_134
135 mod_135 v2_135
136 mod_136 v2_136
137 mod_137 v2_137
138 mod_138 v2_138
139 mod_139 v2_139
14 mod_14 v2_14
140 mod_140 v2_140
141 mod_141 v2_141
142 mod_142 v2_142
143 mod_143 v2_143
144 mod_144 v2_144
145 mod_145 v2_145
146 mod_146 v2_146
147 mod_147 v2_147
148 mod_148 v2_148
149 mod_149 v2_149
15 mod_15 v2_15
150 mod_150 v2_150
151 mod_151 v2_151
152 mod_152 v2_152
153 mod_153 v2_153
154 mod_154 v2_154
155 mod_155 v2_155
156 mod_156 v2_156
157 mod_157 v2_157
158 mod_158 v2_158
159 mod_159 v2_159
16 mod_16 v2_16
160 mod_160 v2_160
161 mod_161 v2_161
162 mod_162 v2_162
163 mod_163 v2_163
164 mod_164 v2_164
165 mod_165 v2_165
166 mod_166 v2_166
167 mod_167 v2_167
168 mod_168 v2_168
169 mod_169 v2_169
17 mod_17 v2_17
170 mod_170 v2_170
171 mod_171 v2_171
172 mod_172 v2_172
173 mod_173 v2_173
174 mod_174 v2_174
175 mod_175 v2_175
176 mod_176 v2_176
177 mod_177 v2_177
178 mod_178 v2_178
179 mod_179 v2_179
18 mod_18 v2_18
180 mod_180 v2_180
181 mod_181 v2_181
182 mod_182 v2_182
183 mod_183 v2_183
184 mod_184 v2_184
185 mod_185 v2_185
186 mod_186 v2_186
187 mod_187 v2_187
188 mod_188 v2_188
189 mod_189 v2_189
19 mod_19 v2_19
190 mod_190 v2_190
191 mod_191 v2_191
192 mod_192 v2_192
193 mod_193 v2_193
194 mod_194 v2_194
195 mod_195 v2_195
196 mod_196 v2_196
197 mod_197 v2_197
198 mod_198 v2_198
199 mod_199 v2_199
2 mod_2 v2_2
20 mod_20 v2_20
200 mod_200 v2_200
201 mod_201 v2_201
202 mod_202 v2_202
203 mod_203 v2_203
204 mod_204 v2_204
205 mod_205 v2_205
206 mod_206 v2_206
207 mod_207 v2_207
208 mod_208 v2_208
209 mod_209 v2_209
21 mod_21 v2_21
210 mod_210 v2_210
211 mod_211 v2_211
212 mod_212 v2_212
213 mod_213 v2_213
214 mod_214 v2_214
215 mod_215 v2_215
216 mod_216 v2_216
217 mod_217 v2_217
218 mod_218 v2_218
219 mod_219 v2_219
22 mod_22 v2_22
220 mod_220 v2_220
221 mod_221 v2_221
222 mod_222 v2_222
223 mod_223 v2_223
224 mod_224 v2_224
225 mod_225 v2_225
226 mod_226 v2_226
227 mod_227 v2_227
228 mod_228 v2_228
229 mod_229 v2_229
23 mod_23 v2_23
230 mod_230 v2_230
231 mod_231 v2_231
232 mod_232 v2_232
233 mod_233 v2_233
234 mod_234 v2_234
235 mod_235 v2_235
236 mod_236 v2_236
237 mod_237 v2_237
238 mod_238 v2_238
239 mod_239 v2_239
24 mod_24 v2_24
240 mod_240 v2_240
241 mod_241 v2_241
242 mod_242 v2_242
243 mod_243 v2_243
244 mod_244 v2_244
245 mod_245 v2_245
246 mod_246 v2_246
247 mod_247 v2_247
248 mod_248 v2_248
249 mod_249 v2_249
25 mod_25 v2_25
250 mod_250 v2_250
251 mod_251 v2_251
252 mod_252 v2_252
253 mod_253 v2_253
254 mod_254 v2_254
255 mod_255 v2_255
26 mod_26 v2_26
27 mod_27 v2_27
28 mod_28 v2_28
29 mod_29 v2_29
3 mod_3 v2_3
30 mod_30 v2_30
31 mod_31 v2_31
32 mod_32 v2_32
33 mod_33 v2_33
34 mod_34 v2_34
35 mod_35 v2_35
36 mod_36 v2_36
37 mod_37 v2_37
38 mod_38 v2_38
39 mod_39 v2_39
4 mod_4 v2_4
40 mod_40 v2_40
41 mod_41 v2_41
42 mod_42 v2_42
43 mod_43 v2_43
44 mod_44 v2_44
45 mod_45 v2_45
46 mod_46 v2_46
47 mod_47 v2_47
48 mod_48 v2_48
49 mod_49 v2_49
5 mod_5 v2_5
50 mod_50 v2_50
51 mod_51 v2_51
52 mod_52 v2_52
53 mod_53 v2_53
54 mod_54 v2_54
55 mod_55 v2_55
56 mod_56 v2_56
57 mod_57 v2_57
58 mod_58 v2_58
59 mod_59 v2_59
6 mod_6 v2_6
60 mod_60 v2_60
61 mod_61 v2_61
62 mod_62 v2_62
63 mod_63 v2_63
64 mod_64 v2_64
65 mod_65 v2_65
66 mod_66 v2_66
67 mod_67 v2_67
68 mod_68 v2_68
69 mod_69 v2_69
7 mod_7 v2_7
70 mod_70 v2_70
71 mod_71 v2_71
72 mod_72 v2_72
73 mod_73 v2_73
74 mod_74 v2_74
75 mod_75 v2_75
76 mod_76 v2_76
77 mod_77 v2_77
78 mod_78 v2_78
79 mod_79 v2_79
8 mod_8 v2_8
80 mod_80 v2_80
81 mod_81 v2_81
82 mod_82 v2_82
83 mod_83 v2_83
84 mod_84 v2_84
85 mod_85 v2_85
86 mod_86 v2_86
87 mod_87 v2_87
88 mod_88 v2_88
89 mod_89 v2_89
9 mod_9 v2_9
90 mod_90 v2_90
91 mod_91 v2_91
92 mod_92 v2_92
93 mod_93 v2_93
94 mod_94 v2_94
95 mod_95 v2_95
96 mod_96 v2_96
97 mod_97 v2_97
98 mod_98 v2_98
99 mod_99 v2_99
HSDELETE
DUMP_TABLE
1 mod_1 v2_1
101 mod_101 v2_101
103 mod_103 v2_103
105 mod_105 v2_105
107 mod_107 v2_107
109 mod_109 v2_109
11 mod_11 v2_11
111 mod_111 v2_111
113 mod_113 v2_113
115 mod_115 v2_115
117 mod_117 v2_117
119 mod_119 v2_119
121 mod_121 v2_121
123 mod_123 v2_123
125 mod_125 v2_125
127 mod_127 v2_127
129 mod_129 v2_129
13 mod_13 v2_13
131 mod_131 v2_131
133 mod_133 v2_133
135 mod_135 v2_135
137 mod_137 v2_137
139 mod_139 v2_139
141 mod_141 v2_141
143 mod_143 v2_143
145 mod_145 v2_145
147 mod_147 v2_147
149 mod_149 v2_149
15 mod_15 v2_15
151 mod_151 v2_151
153 mod_153 v2_153
155 mod_155 v2_155
157 mod_157 v2_157
159 mod_159 v2_159
161 mod_161 v2_161
163 mod_163 v2_163
165 mod_165 v2_165
167 mod_167 v2_167
169 mod_169 v2_169
17 mod_17 v2_17
171 mod_171 v2_171
173 mod_173 v2_173
175 mod_175 v2_175
177 mod_177 v2_177
179 mod_179 v2_179
181 mod_181 v2_181
183 mod_183 v2_183
185 mod_185 v2_185
187 mod_187 v2_187
189 mod_189 v2_189
19 mod_19 v2_19
191 mod_191 v2_191
193 mod_193 v2_193
195 mod_195 v2_195
197 mod_197 v2_197
199 mod_199 v2_199
201 mod_201 v2_201
203 mod_203 v2_203
205 mod_205 v2_205
207 mod_207 v2_207
209 mod_209 v2_209
21 mod_21 v2_21
211 mod_211 v2_211
213 mod_213 v2_213
215 mod_215 v2_215
217 mod_217 v2_217
219 mod_219 v2_219
221 mod_221 v2_221
223 mod_223 v2_223
225 mod_225 v2_225
227 mod_227 v2_227
229 mod_229 v2_229
23 mod_23 v2_23
231 mod_231 v2_231
233 mod_233 v2_233
235 mod_235 v2_235
237 mod_237 v2_237
239 mod_239 v2_239
241 mod_241 v2_241
243 mod_243 v2_243
245 mod_245 v2_245
247 mod_247 v2_247
249 mod_249 v2_249
25 mod_25 v2_25
251 mod_251 v2_251
253 mod_253 v2_253
255 mod_255 v2_255
27 mod_27 v2_27
29 mod_29 v2_29
3 mod_3 v2_3
31 mod_31 v2_31
33 mod_33 v2_33
35 mod_35 v2_35
37 mod_37 v2_37
39 mod_39 v2_39
41 mod_41 v2_41
43 mod_43 v2_43
45 mod_45 v2_45
47 mod_47 v2_47
49 mod_49 v2_49
5 mod_5 v2_5
51 mod_51 v2_51
53 mod_53 v2_53
55 mod_55 v2_55
57 mod_57 v2_57
59 mod_59 v2_59
61 mod_61 v2_61
63 mod_63 v2_63
65 mod_65 v2_65
67 mod_67 v2_67
69 mod_69 v2_69
7 mod_7 v2_7
71 mod_71 v2_71
73 mod_73 v2_73
75 mod_75 v2_75
77 mod_77 v2_77
79 mod_79 v2_79
81 mod_81 v2_81
83 mod_83 v2_83
85 mod_85 v2_85
87 mod_87 v2_87
89 mod_89 v2_89
9 mod_9 v2_9
91 mod_91 v2_91
93 mod_93 v2_93
95 mod_95 v2_95
97 mod_97 v2_97
99 mod_99 v2_99
